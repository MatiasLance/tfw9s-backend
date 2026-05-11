<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Modules\Categories\CategoryServiceInterface;
use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    protected ItemServiceInterface $itemService;

    protected CategoryServiceInterface $categoryService;

    public function __construct(ItemServiceInterface $itemService, CategoryServiceInterface $categoryService)
    {
        $this->itemService = $itemService;
        $this->categoryService = $categoryService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $category = $request->query('category', null);
        $tag = $request->query('tags', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $itemVariant = $request->query('itemVariant', null);
        $maxItemsPerPage = $request->query('maxItemsPerPage', null);

        $filter = [
            'q' => $query,
            'category' => $category,
            'tag' => $tag,
            'sort' => $sort,
            'page' => $page,
            'itemVariant' => is_null($itemVariant) ? $itemVariant : intval($itemVariant),
            'max_item_per_page' => is_null($maxItemsPerPage) ? $maxItemsPerPage : intval($maxItemsPerPage),
        ];

        $paginatedItems = $this->itemService->listItems($filter);

        $message->setContent(200, 'Items retrieved', '', $paginatedItems->toArray());

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

        $colors = [];
        if ($request->has('colors')) {
            $decoded = json_decode($request->input('colors'), true);
            if (is_array($decoded)) {
                $colors = array_values(array_filter($decoded, 'is_string'));
            }
        }
        
        $categories = array_map(function($id) {
            return $this->categoryService->retrieveCategory(intval($id));
        }, $categoryId);

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
            $colors
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
        $tags = $request->input('tags') ?? null;
        $isFeatured = $request->boolean('isFeatured') ?? null;
        $isRRP = $request->boolean('isRRP') ?? null;
        $isOnSale = $request->boolean('isOnSale') ?? null;
        $photo = $request->file('photo') ?? null;
        $categoryId = $request->input('categoryId') ?? null;

        if (!is_null($categoryId)) {
            $categories = array_map(function($id) {
                return $this->categoryService->retrieveCategory(intval($id));
            }, $categoryId);
        } else {
            $categories = null;
        }

        $newItem = $this->itemService->duplicateItem($itemId, $name, $description, $price, $saleprice, $stock, $isFeatured, $isRRP, $isOnSale, $photo, $categories, $tags);

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
        $tags = $request->input('tags') ?? null;
        $isFeatured = $request->boolean('isFeatured') ?? null;
        $isRRP = $request->boolean('isRRP') ?? null;
        $isOnSale = $request->boolean('isOnSale') ?? null;
        $isHideOutOfStock = $request->boolean('isHideOutOfStock') ?? null;
        $photo = $request->file('photo') ?? null;
        $categoryId = $request->input('categoryId') ?? null;

        if (!is_null($categoryId)) {
            $categories = array_map(function($id) {
                return $this->categoryService->retrieveCategory(intval($id));
            }, $categoryId);
        } else {
            $categories = null;
        }

        $newItem = $this->itemService->addItemVariant($itemId, $name, $description, $price, $saleprice, $stock, $isFeatured, $isRRP, $isOnSale, $isHideOutOfStock, $photo, $categories, $tags);

        if ($newItem instanceof Item) {
            $message->setContent(201, 'Item added as variant', '', [
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

        $colors = [];
        if ($request->has('colors')) {
            $decoded = json_decode($request->input('colors'), true);
            if (is_array($decoded)) {
                $colors = array_values(array_filter($decoded, 'is_string'));
            }
        }

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
            $colors
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
}
