<?php

namespace App\Modules\Item\Tests;

use App\Models\Category;
use App\Models\Item;
use Tests\TestCase;

class ItemModelTest extends TestCase
{
    public function test_trashed_categories()
    {
        $item = Item::factory()->hasCategories(2)->create();
        $category = Category::factory()->create();
        
        $item->categories()->save($category);

        $category->delete();

        $itemCategories = $item->categories->pluck('id');
        
        $this->assertTrue($category->trashed());
    }
}
