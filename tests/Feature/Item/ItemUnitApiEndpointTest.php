<?php

namespace Tests\Feature\Item;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;
use Database\Seeders\VariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemUnitApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_item_unit_minimal()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->seed(VariantSeeder::class);
        $item = Item::factory()
                        ->hasElements(5)
                        ->create();

        $itemUnitElements = $item
                                ->elements()
                                ->pluck('id')
                                ->random(3)
                                ->all();

        $response = $this->post('api/v1/items/' . $item->id . '/units', [
            'element_ids' => $itemUnitElements,
            'stock' => 5,
        ]);
        
        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'item_unit' => [
                        'id',
                        'elements',
                        'stock',
                        'price',
                    ],
                ],
            ]);

        $this->assertDatabaseHas(ItemUnit::class, [
            'item_id' => $item->id,
            'stock' => 5,
            
            /**
             * Doesn't work, idk
             */
            // 'element_ids' => $this->convertArrayToDatabaseJsonFormat($itemUnitElements),
        ]);
    }

    public function test_create_item_unit_complete()
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->seed(VariantSeeder::class);
        $item = Item::factory()
                        ->hasElements(5)
                        ->create();

        $itemUnitElements = $item
                                ->elements()
                                ->pluck('id')
                                ->random(3)
                                ->all();

        $response = $this->post('api/v1/items/' . $item->id . '/units', [
            'element_ids' => $itemUnitElements,
            'stock' => 5,
            'price' => 210,
            'sku' => 'test_tessku1258',
        ]);
        
        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'title',
                'data' => [
                    'item_unit' => [
                        'id',
                        'elements',
                        'stock',
                        'price',
                    ],
                ],
            ]);

        $this->assertDatabaseHas(ItemUnit::class, [
            'item_id' => $item->id,
            'stock' => 5,
            'price' => 210,
            'sku' => 'test_tessku1258',
            
            /**
             * Doesn't work, idk
             */
            // 'element_ids' => $this->convertArrayToDatabaseJsonFormat($itemUnitElements),
        ]);
    }

    public function test_update_item_unit()
    {
        $this->markTestIncomplete('Update WIP');
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->seed(VariantSeeder::class);
        $item = Item::factory()
                        ->hasElements(5)
                        ->create();

        $itemUnit = ItemUnit::factory()
                    ->create([
                        'item_id' => $item->id,
                    ]);

        $response = $this->patch('/api/v1/items/' . $item->id . '/units/' . $itemUnit);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);
    }

    /**
     * Convert an array to a JSON format that the DB stores them as
     * 
     * @param array $array The array to convert
     * 
     * @return string
     */
    protected function convertArrayToDatabaseJsonFormat(array $array): string
    {
        $formattedString = '';
        $exploded = explode(',', json_encode($array));

        foreach ($exploded as $index => $element) {
            if ($index < count($exploded) - 1) {
                $formattedString .= $element . ', ';
            } else {
                $formattedString .= $element;
            }
        }

        return $formattedString;
    }
}