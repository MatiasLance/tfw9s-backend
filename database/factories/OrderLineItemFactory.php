<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderLineItem>
 */
class OrderLineItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        if (Item::count() > 3) {
            $itemId = Item::get()->pluck('id')->random();
            $item = Item::find($itemId);
        } else {
            $item = Item::factory()->create();
        }
        
        return [
            'order_id' => 1,
            'item_id' => $item->id,
            'price' => $item->price,
            'quantity' => $this->faker->numberBetween(1, 25),
        ];
    }
}
