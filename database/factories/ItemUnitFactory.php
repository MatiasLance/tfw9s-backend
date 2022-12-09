<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemUnit>
 */
class ItemUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $item = Item::all()->random();
        $elements = $item->elements->pluck('id');
        $elementIds = $this->faker->randomElements($elements, $this->faker->numberBetween(1, 3));
        $overridePrice = $this->faker->boolean(10);

        return [
            'item_id' => $item->id,
            'element_ids' => $elementIds,
            'price' => $overridePrice ? $this->faker->numberBetween(10, 300) * 10 : null,
            'stock' => $this->faker->numberBetween(0, 20),
            'sku' => $this->faker->unique()->regexify('test-[a-f0-9]{8}'),
        ];
    }
}
