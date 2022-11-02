<?php

namespace App\Modules\Item\Tests;

use App\Models\Item;
use App\Modules\Item\Filter;
use App\Repository\ItemRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Item Repository
     * 
     * @var ItemRepositoryInterface $itemRepository
     */
    protected ItemRepositoryInterface $itemRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->itemRepository = $this->app->make(ItemRepositoryInterface::class);
    }
    
    public function test_sort_item_a_to_z()
    {
        Item::factory()->createMany([
            [
                'name' => 'B'
            ],
            [
                'name' => 'A Item'
            ],
            [
                'name' => '120cc'
            ],
            [
                'name' => 'Ac'
            ],
        ]);

        $items = $this->itemRepository->listItems([
            'sort' => Filter::SORT_A_TO_Z
        ]);


        $itemIds = array_map(function($item) {
            return $item['id'];
        }, $items->toArray()['items']);

        $expectedIds = [3, 2, 4, 1];

        $this->assertEquals(json_encode($expectedIds), json_encode($itemIds));
    }
}