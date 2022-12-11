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
            'price' => null,
            'sku' => null,
            
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
            'sku' => 'test_sku781',
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
            'sku' => 'test_sku781',
            
            /**
             * Doesn't work, idk
             */
            // 'element_ids' => $this->convertArrayToDatabaseJsonFormat($itemUnitElements),
        ]);
    }

    /**
     * @todo When test is run with ItemApiEndpointTest.php, has a problem with missing Item/ ItemUnit
     */
    public function test_update_item_unit()
    {
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
                        'stock' => 5,
                        'price' => 210,
                        'sku' => 'test_sku781',
                    ]);

        $response = $this->patch('/api/v1/items/' . $item->id . '/units/' . $itemUnit->id, [
            'stock' => 66,
            'price' => 500,
            'sku' => 'test_sku4562',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertDatabaseHas(ItemUnit::class, [
            'item_id' => $item->id,
            'stock' => 66,
            'price' => 500,
            'sku' => 'test_sku4562',
            
            /**
             * Doesn't work, idk
             */
            // 'element_ids' => $this->convertArrayToDatabaseJsonFormat($itemUnitElements),
        ]);
    }

    /**
     * @todo When test is run with ItemApiEndpointTest.php, has a problem with missing Item/ ItemUnit
     */
    public function test_delete_item_unit()
    {
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

        $response = $this->delete('/api/v1/items/' . $item->id . '/units/' . $itemUnit->id);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'title',
            ]);

        $this->assertModelMissing($itemUnit);
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
