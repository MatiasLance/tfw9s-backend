<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemUnit;
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

        $filter = [
            'q' => $query,
            'category' => $category,
            'tag' => $tag,
            'sort' => $sort,
            'page' => $page,
        ];

        $paginatedItems = $this->itemService->listItems($filter);

        $message->setContent(200, 'Items retrieved', '', $paginatedItems->toArray());

        return $message->render();
    }

    public function retrieve(Request $request, Message $message, int $itemId)
    {
        $itemData = $this->itemService->retrieveItem($itemId);

        $message->setContent(200, 'Item retrieved', '', $itemData);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $user = $request->user();

        $name = $request->input('name');
        $description = $request->input('description');
        $price = $request->input('price');

        if (is_numeric($price)) {
            $price = floatval($price);
        }

        $elements = $request->input('elements') ?? [];
        if (gettype($elements) == 'string') { // Accept json encoded strings
            $elements = json_decode($elements);
        }
        $imageThumbnails = $request->file('elements') ?? [];

        $elements = $this->mergeElementImageThumbnails($elements, $imageThumbnails);

        $tags = $request->input('tags');
        $photo = $request->file('photo') ?? [];
        $categoryId = $request->input('categoryId') ?? [];
        $categories = array_map(function($id) {
            return $this->categoryService->retrieveCategory(intval($id));
        }, $categoryId);

        $item = $this->itemService->createItem($name, $description, $price, $elements, $photo, $categories, $tags);

        if ($item instanceof Item) {
            $message->setContent(201, 'Item created', '', [
                'item' => $item
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

        $elements = $request->input('elements') ?? [];
        if (gettype($elements) == 'string') { // Accept json encoded strings
            $elements = json_decode($elements);
        }
        $imageThumbnails = $request->file('elements') ?? [];

        $elements = $this->mergeElementImageThumbnails($elements, $imageThumbnails);

        $tags = $request->input('tags') ?? null;
        $photo = $request->file('photo') ?? null;
        $categoryId = $request->input('categoryId') ?? null;

        if (!is_null($categoryId)) {
            $categories = array_map(function($id) {
                return $this->categoryService->retrieveCategory(intval($id));
            }, $categoryId);
        } else {
            $categories = null;
        }

        $newItem = $this->itemService->duplicateItem($itemId, $name, $description, $price, $elements, $photo, $categories, $tags);

        if ($newItem instanceof Item) {
            $message->setContent(201, 'Item duplicated', '', [
                'item' => $newItem
            ]);
        } else {
            $message->setContent(400, 'Item duplication failed');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $user = $request->user();

        $name = $request->input('name');
        $description = $request->input('description');
        $price = $request->input('price');

        if (is_numeric($price)) {
            $price = floatval($price);
        }

        $elements = $request->input('elements') ?? [];
        if (gettype($elements) == 'string') { // Accept json encoded strings
            $elements = json_decode($elements);
        }
        $imageThumbnails = $request->file('elements') ?? [];

        $elements = $this->mergeElementImageThumbnails($elements, $imageThumbnails);

        $tags = $request->input('tags');
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

        $isSuccess = $this->itemService->updateItem($id, $name, $description, $price, $elements, $photo, $categories, $tags);

        if ($isSuccess) {
            $message->setContent(200, 'Item updated');
        } else {
            $message->setContent(400, 'Item not updated');
        }

        return $message->render();
    }

    public function createItemUnit(Request $request, Message $message, int $itemId)
    {
        $user = $request->user();
        
        $elementIds = $request->input('element_ids') ?? [];
        $price = $request->input('price') ?? null;
        $stock = $request->input('stock');
        $sku = $request->input('sku');

        $itemUnit = $this->itemService->createItemUnit($itemId, $elementIds, $price, $stock, $sku);

        if ($itemUnit instanceof ItemUnit) {
            $message->setContent(
                status: 201,
                title: 'Item unit created',
                data: [
                    'item_unit' => $itemUnit
                ]
            );
        } else {
            $message->setContent(
                status: 400,
                title: 'Item unit cannot be created'
            );
        }

        return $message->render();
    }

    public function updateItemUnit(Request $request, Message $message, int $itemId, int $unitId)
    {
        $user = $request->user();
        
        $elementIds = $request->input('element_ids') ?? [];
        $price = $request->input('price') ?? null;
        $stock = $request->input('stock');
        $sku = $request->input('sku');

        $isSuccess = $this->itemService->updateItemUnit($unitId, $itemId, $elementIds, $price, $stock, $sku);

        if ($isSuccess) {
            $message->setContent(
                status: 200,
                title: 'Item unit updated'
            );
        } else {
            $message->setContent(
                status: 400,
                title: 'Item unit cannot be updated'
            );
        }

        return $message->render();
    }

    public function deleteItemUnit(Request $request, Message $message, int $itemId, int $unitId)
    {
        $user = $request->user();
     
        $isSuccess = $this->itemService->deleteItemUnit($unitId, $itemId);

        if ($isSuccess) {
            $message->setContent(
                status: 200,
                title: 'Item unit deleted'
            );
        } else {
            $message->setContent(
                status: 400,
                title: 'Failed to delete Item unit'
            );
        }

        return $message->render();
    }


    public function delete(Request $request, Message $message, int $id)
    {
        $user = $request->user();
        $isSuccess = $this->itemService->deleteItem($user, $id);

        if ($isSuccess) {
            $message->setContent(200, 'Item updated');
        } else {
            $message->setContent(400, 'Item not updated');
        }

        return $message->render();
    }

    /**
     * Merge the image thumbnails to the element array
     * 
     * @param array $elements The array containing the element data
     * @param array $imageThumbnails The array containing the images
     * 
     * @return array
     */
    protected function mergeElementImageThumbnails(array $elements, array $imageThumbnails): array
    {
        foreach ($imageThumbnails as $elementKey => $imageThumbnail) {
            $elements[$elementKey]['thumbnail'] = $imageThumbnail['thumbnail'];
        }
        return $elements;
    }
}
