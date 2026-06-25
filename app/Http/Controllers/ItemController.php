<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Modules\Categories\CategoryServiceInterface;
use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Services\NotifyService;

class ItemController extends Controller
{
    protected ItemServiceInterface $itemService;

    protected CategoryServiceInterface $categoryService;

    public function __construct(ItemServiceInterface $itemService, CategoryServiceInterface $categoryService, NotifyService $notifyService)
    {
        $this->itemService = $itemService;
        $this->categoryService = $categoryService;
        $this->notifyService = $notifyService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $category = $request->query('category', null);
        $tag = $request->query('tags', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $itemVariant = $request->query('item_variant', null);
        $maxItemsPerPage = $request->query('maxItemsPerPage', null);

        $filter = [
            'q' => $query,
            'category' => $category,
            'tag' => $tag,
            'sort' => $sort,
            'page' => $page,
            'item_variant' => is_null($itemVariant) ? $itemVariant : intval($itemVariant),
            'max_item_per_page' => is_null($maxItemsPerPage) ? $maxItemsPerPage : intval($maxItemsPerPage),
        ];

        $paginatedItems = $this->itemService->listItems($filter);

        $message->setContent(200, 'Items retrieved', '', $paginatedItems->toArray());
        
        $this->notifyService->sendNotificationForItemList($message->render());

        return $message->render();
    }

    public function retrieve(Request $request, Message $message, int $itemId)
    {
        $item = $this->itemService->retrieveItem($itemId);

        $message->setContent(200, 'Item retrieved', '', [
            'item' => $item
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $user = $request->user();

        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $price = $request->input('price');
        if (is_numeric($price)) {
            $price = floatval($price);
        }
        $saleprice = $request->input('salePrice');
        if (is_numeric($saleprice)) {
            $saleprice = floatval($saleprice);
        }
        $stock = $request->input('stock');
        if (is_numeric($stock)) {
            $stock = intval($stock);
        }
        $tags = $request->input('tags') ?? [];
        $isFeatured = $request->boolean('isFeatured');
        $isRRP = $request->boolean('isRRP');
        $isOnSale = $request->boolean('isOnSale');
        $isHideOutOfStock = $request->boolean('isHideOutOfStock');
        $photo = $request->file('photo') ?? [];
        $categoryId = $request->input('categoryId') ?? [];
        $shippingId = $request->input('selected_shippingid');
        
        $sizeVariants = [];
        if ($request->has('size_variants')) {
            $sizeVariants = json_decode($request->input('size_variants'), true) ?? [];
        }

        $colorVariants = [];
        if ($request->has('color_variants')) {
            $decoded = json_decode($request->input('color_variants'), true);
            
            if (is_array($decoded)) {
                foreach ($decoded as $index => $color) {
                    if (!is_array($color) || empty($color['name'])) {
                        continue;
                    }
                    
                    $colorVariants[] = [
                        'id' => $color['id'] ?? null,
                        'name' => trim($color['name']),
                        'hexcode' => strtoupper(trim($color['hexcode'] ?? '')),
                        'use_image' => (bool) ($color['use_image'] ?? false),
                        'is_active' => (bool) ($color['is_active'] ?? true),
                        'sort_order' => $color['sort_order'] ?? $index,
                        'price_override' => $this->sanitizeNumeric($color['price_override'] ?? null, 'float'),
                        'stock_quantity' => $this->sanitizeNumeric($color['stock_quantity'] ?? 0, 'int', 0),
                        'sku' => $color['sku'] ?? null,
                        'sku_suffix' => $color['sku_suffix'] ?? null,
                    ];
                }
            }
        }

        $uploadedColorImages = [];
        if ($request->hasFile('color_images')) {
            foreach ($request->file('color_images') as $key => $file) {
                $uploadedColorImages[$key] = $file;
            }
        }
        
        $categories = array_map(function($id) {
            return $this->categoryService->retrieveCategory(intval($id));
        }, $categoryId);

        $has_shipping = $request->input('has_shipping') ?? false;
        $shipping_charge = $request->input('shipping_charge') ?? 0;

        $item = $this->itemService->createItem(
            $name, 
            $description, 
            $price, 
            $saleprice, 
            $stock, 
            $isFeatured, 
            $isRRP, 
            $isOnSale, 
            $isHideOutOfStock, 
            $photo, 
            $categories, 
            $shippingId, 
            $tags,
            $sizeVariants,
            $colorVariants,
            $uploadedColorImages,
            $has_shipping,
            $shipping_charge
        );

        if ($item instanceof Item) {
            $message->setContent(201, 'Item created', '', [
                'item' => $item->load(['sizeVariants', 'colorVariants', 'categories', 'media'])
            ]);
        } else {
            $message->setContent(400, 'Item not created');
        }

        return $message->render();
    }

    public function duplicate(Request $request, Message $message, int $itemId)
    {
        $user = $request->user();

        $name = $request->input('name') ?? null;
        $description = $request->input('description') ?? null;
        $price = $request->input('price') ?? null;
        if (is_numeric($price)) {
            $price = floatval($price);
        }
        $saleprice = $request->input('salePrice') ?? null;
        if (is_numeric($saleprice)) {
            $saleprice = floatval($saleprice);
        }
        $stock = $request->input('stock') ?? null;
        if (is_numeric($stock)) {
            $stock = intval($stock);
        }
        $isFeatured = $request->boolean('isFeatured') ?? null;
        $isRRP = $request->boolean('isRRP') ?? null;
        $isOnSale = $request->boolean('isOnSale') ?? null;
        $photo = $request->file('photo') ?? null;
        $categoryId = $request->input('categoryId') ?? null;

        $sizeVariants = [];
        if ($request->has('size_variants')) {
            $sizeVariants = json_decode($request->input('size_variants'), true) ?? [];
        }

        $colorVariants = [];
        if ($request->has('color_variants')) {
            $decoded = json_decode($request->input('color_variants'), true);
            
            if (is_array($decoded)) {
                foreach ($decoded as $index => $color) {
                    if (!is_array($color) || empty($color['name'])) {
                        continue;
                    }
                    
                    $colorVariants[] = [
                        'id' => $color['id'] ?? null,
                        'name' => trim($color['name']),
                        'hexcode' => strtoupper(trim($color['hexcode'] ?? '')),
                        'use_image' => (bool) ($color['use_image'] ?? false),
                        'is_active' => (bool) ($color['is_active'] ?? true),
                        'sort_order' => $color['sort_order'] ?? $index,
                        'price_override' => $this->sanitizeNumeric($color['price_override'] ?? null, 'float'),
                        'stock_quantity' => $this->sanitizeNumeric($color['stock_quantity'] ?? 0, 'int', 0),
                        'sku' => $color['sku'] ?? null,
                        'sku_suffix' => $color['sku_suffix'] ?? null,
                    ];
                }
            }
        }

        $uploadedColorImages = [];
        if ($request->hasFile('color_images')) {
            foreach ($request->file('color_images') as $key => $file) {
                $uploadedColorImages[$key] = $file;
            }
        }

        if (!is_null($categoryId)) {
            $categories = array_map(function($id) {
                return $this->categoryService->retrieveCategory(intval($id));
            }, $categoryId);
        } else {
            $categories = null;
        }

        $has_shipping = $request->input('has_shipping') ?? false;
        $shipping_charge = $request->input('shipping_charge') ?? 0;

        $newItem = $this->itemService->duplicateItem(
                $itemId,
                $name,
                $description,
                $price,
                $saleprice,
                $stock,
                $isFeatured,
                $isRRP,
                $isOnSale,
                $photo,
                $categories,
                $sizeVariants,
                $colorVariants,
                $uploadedColorImages,
                $has_shipping,
                $shipping_charge
            );

        if ($newItem instanceof Item) {
            $message->setContent(201, 'Item duplicated', '', [
                'item' => $newItem
            ]);
        } else {
            $message->setContent(400, 'Item duplication failed');
        }

        return $message->render();
    }

    public function storeItemVariant(Request $request, Message $message, int $itemId)
    {
        $name = $request->input('name') ?? null;
        $description = $request->input('description') ?? null;
        $price = $request->input('price') ?? null;
        if (is_numeric($price)) {
            $price = floatval($price);
        }
        $saleprice = $request->input('salePrice') ?? null;
        if (is_numeric($saleprice)) {
            $saleprice = floatval($saleprice);
        }
        $stock = $request->input('stock') ?? null;
        if (is_numeric($stock)) {
            $stock = intval($stock);
        }
        $isFeatured = $request->boolean('isFeatured') ?? null;
        $isRRP = $request->boolean('isRRP') ?? null;
        $isOnSale = $request->boolean('isOnSale') ?? null;
        $isHideOutOfStock = $request->boolean('isHideOutOfStock') ?? null;
        $photo = $request->file('photo') ?? null;
        $categoryId = $request->input('categoryId') ?? null;
        
        $sizeVariants = [];
        if ($request->has('size_variants')) {
            $sizeVariants = json_decode($request->input('size_variants'), true) ?? [];
        }

        $colorVariants = [];
        if ($request->has('color_variants')) {
            $decoded = json_decode($request->input('color_variants'), true);
            
            if (is_array($decoded)) {
                foreach ($decoded as $index => $color) {
                    if (!is_array($color) || empty($color['name'])) {
                        continue;
                    }
                    
                    $colorVariants[] = [
                        'id' => $color['id'] ?? null,
                        'name' => trim($color['name']),
                        'hexcode' => strtoupper(trim($color['hexcode'] ?? '')),
                        'use_image' => (bool) ($color['use_image'] ?? false),
                        'is_active' => (bool) ($color['is_active'] ?? true),
                        'sort_order' => $color['sort_order'] ?? $index,
                        'price_override' => $this->sanitizeNumeric($color['price_override'] ?? null, 'float'),
                        'stock_quantity' => $this->sanitizeNumeric($color['stock_quantity'] ?? 0, 'int', 0),
                        'sku' => $color['sku'] ?? null,
                        'sku_suffix' => $color['sku_suffix'] ?? null,
                    ];
                }
            }
        }

        $uploadedColorImages = [];
        if ($request->hasFile('color_images')) {
            foreach ($request->file('color_images') as $key => $file) {
                $uploadedColorImages[$key] = $file;
            }
        }

        if (!is_null($categoryId)) {
            $categories = array_map(function($id) {
                return $this->categoryService->retrieveCategory(intval($id));
            }, $categoryId);
        } else {
            $categories = null;
        }

        $has_shipping = $request->input('has_shipping') ?? false;
        $shipping_charge = $request->input('shipping_charge') ?? 0;

        $newItem = $this->itemService->addItemVariant(
            $itemId,
            $name,
            $description,
            $price,
            $saleprice,
            $stock,
            $isFeatured,
            $isRRP,
            $isOnSale,
            $isHideOutOfStock,
            $photo,
            $categories,
            $sizeVariants,
            $colorVariants,
            $uploadedColorImages,
            $has_shipping,
            $shipping_charge
        );

        if ($newItem instanceof Item) {
            $message->setContent(201, 'Variant added', '', [
                'item' => $newItem
            ]);
        } else {
            $message->setContent(400, 'Failed to add item as variant');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $user = $request->user();

        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $price = $request->input('price');
        if (is_numeric($price)) {
            $price = floatval($price);
        }
        $saleprice = $request->input('salePrice');
        if (is_numeric($saleprice)) {
            $saleprice = floatval($saleprice);
        }
        $stock = $request->input('stock');
        if (is_numeric($stock)) {
            $stock = intval($stock);
        }
        $tags = $request->input('tags') ?? [];
        $isFeatured = $request->boolean('isFeatured');
        $isRRP = $request->boolean('isRRP');
        $isOnSale = $request->boolean('isOnSale');
        $isHideOutOfStock = $request->boolean('isHideOutOfStock');
        $newPhoto = $request->file('photo') ?? [];
        $existingPhoto = $request->input('photo') ?? [];
        $newPhotoCount = count($newPhoto);
        $existingPhotoCount = count($existingPhoto);

        if (
            $request->has('photo') &&
            (
                $newPhotoCount > 0 ||
                $existingPhotoCount > 0
            )
        ) {
            foreach ($existingPhoto as $existingPhotoHash) {
                array_push($newPhoto, $existingPhotoHash);
            }
            $photo = $newPhoto;
        } else {
            $photo = null;
        }

        $categoryId = $request->input('categoryId') ?? [];
        $categories = array_map(function($id) {
            return $this->categoryService->retrieveCategory(intval($id));
        }, $categoryId);

        $shippingId = $request->input('selected_shippingid');

        $sizeVariants = [];
        if ($request->has('size_variants')) {
            $sizeVariants = json_decode($request->input('size_variants'), true) ?? [];
        }

        $colorVariants = [];
        if ($request->has('color_variants')) {
            $decoded = json_decode($request->input('color_variants'), true);
            
            if (is_array($decoded)) {
                foreach ($decoded as $index => $color) {
                    if (!is_array($color) || empty($color['name'])) {
                        continue;
                    }
                    
                    $colorVariants[] = [
                        'id' => $color['id'] ?? null,
                        'name' => trim($color['name']),
                        'hexcode' => strtoupper(trim($color['hexcode'] ?? '')),
                        'use_image' => (bool) ($color['use_image'] ?? false),
                        'is_active' => (bool) ($color['is_active'] ?? true),
                        'sort_order' => $color['sort_order'] ?? $index,
                        'price_override' => $this->sanitizeNumeric($color['price_override'] ?? null, 'float'),
                        'stock_quantity' => $this->sanitizeNumeric($color['stock_quantity'] ?? 0, 'int', 0),
                        'sku' => $color['sku'] ?? null,
                        'sku_suffix' => $color['sku_suffix'] ?? null,
                    ];
                }
            }
        }

        $uploadedColorImages = [];
        if ($request->hasFile('color_images')) {
            foreach ($request->file('color_images') as $key => $file) {
                $uploadedColorImages[$key] = $file;
            }
        }

        $has_shipping = $request->input('has_shipping') ?? false;
        $shipping_charge = $request->input('shipping_charge') ?? 0;

        $isSuccess = $this->itemService->updateItem(
            $id, 
            $name, 
            $description, 
            $price, 
            $saleprice, 
            $stock, 
            $isFeatured, 
            $isRRP, 
            $isOnSale, 
            $isHideOutOfStock, 
            $photo, 
            $categories, 
            $shippingId, 
            $tags,
            $sizeVariants,
            $colorVariants,
            $uploadedColorImages,
            $has_shipping,
            $shipping_charge
        );

        if ($isSuccess) {
            $message->setContent(200, 'Item updated');
        } else {
            $message->setContent(400, 'Item not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {
        $user = $request->user();
        $item = $this->itemService->retrieveItem($id);

        $isSuccess = $this->itemService->deleteItem($user, $item);

        if ($isSuccess) {
            $message->setContent(200, 'Item deleted');
        } else {
            $message->setContent(400, 'Item not deleted');
        }

        return $message->render();
    }

    /**
     * Toggle the active state of an item.
     *
     * Makes an item visible or hidden to end users. Requires ownership
     * or explicit permission to modify the item.
     */
    public function toggleItemStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id'        => ['required', 'integer', 'exists:items,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $item = Item::find($validated['id']);

        if ($item->is_active === $validated['is_active']) {
            return response()->json([
                'message'  => $item->is_active
                    ? 'Item is already visible.'
                    : 'Item is already hidden.',
                'is_active' => $item->is_active,
            ]);
        }

        $updated = DB::table('items')
            ->where('id', $validated['id'])
            ->update(['is_active' => $validated['is_active']]);

        if (! $updated) {
            Log::warning('Item status toggle had no effect.', [
                'item_id'   => $validated['id'],
                'user_id'   => $request->user()?->id,
                'is_active' => $validated['is_active'],
            ]);

            return response()->json([
                'message' => 'Unable to update item status. Please try again.',
            ], 500);
        }

        $item->refresh();

        Log::info('Item status toggled.', [
            'item_id'   => $item->id,
            'user_id'   => $request->user()?->id,
            'is_active' => $item->is_active,
        ]);

        return response()->json([
            'message'   => $item->is_active
                ? 'Item is now visible to users.'
                : 'Item is now hidden from users.',
            'is_active' => $item->is_active,
        ]);
    }

    /**
     * Sanitize and cast a value to a strict numeric type.
     *
     * @param mixed $value
     * @param 'int'|'float' $type
     * @param int|float $default
     * @return int|float
     */
    protected function sanitizeNumeric(mixed $value, string $type = 'int', int|float $default = 0): int|float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $clean = is_string($value) ? trim($value) : $value;

        if (!is_numeric($clean)) {
            return $default;
        }

        if ($type === 'int') {
            return (int) round((float) $clean);
        }

        return (float) $clean;
    }
}
