<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Modules\Http\Message;
use App\Modules\Item\ItemServiceInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Item service
     * 
     * @var ItemServiceInterface $itemService
     */
    protected ItemServiceInterface $itemService;

    public function __construct(ItemServiceInterface $itemService)
    {
        $this->itemService = $itemService;
    }
    
    public function list(Request $request, Message $message)
    {
        $categories = $this->itemService->listCategories();
        $total_categories = $this->itemService->countCategories();

        $message->setContent(200, 'Categories retrieved', '', [
            'categories' => $categories,
            'total_categories' => $total_categories
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $user = $request->user();
        $name = $request->input('name');
        $parentId = $request->input('parentId') ?? null;

        if (is_numeric($parentId)) {
            $parentId = intval($parentId);
        }

        $category = $this->itemService->createCategory($name, $parentId);

        if ($category instanceof Category) {
            $message->setContent(201, 'Category created', '', [
                'category' => $category
            ]);
        } else {
            $message->setContent(400, 'Category create failed');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $user = $request->user();
        $name = $request->input('name');
        $parentId = $request->input('parentId') ?? null;

        if (is_numeric($parentId)) {
            $parentId = intval($parentId);
        }
        
        $isSuccess = $this->itemService->updateCategory($id, $name, $parentId);

        if ($isSuccess) {
            $message->setContent(200, 'Category updated');
        } else {
            $message->setContent(400, 'Category update failed');
        }

        return $message->render();
    }

    public function move(Request $request, Message $message)
    {
        $categories = $request->input('categories');
        $categories = array_map(fn($category) => intval($category), $categories);
        $target = $request->input('target', null);
        if (!is_null($target) && is_numeric($target)) {
            $target = intval($target);
        }

        $isSuccess = $this->itemService->moveCategory($categories, $target);

        if ($isSuccess) {
            $message->setContent(200, 'Categories moved successfully');
        } else {
            $message->setContent(400, 'Category move failed');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {
        $user = $request->user();
        
        $isSuccess = $this->itemService->deleteCategory($user, $id);

        if ($isSuccess) {
            $message->setContent(200, 'Category deleted');
        } else {
            $message->setContent(400, 'Category delete failed');
        }

        return $message->render();
    }
}
