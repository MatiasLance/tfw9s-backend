<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $price = $this->faker->numberBetween(1, 15) * 10;
        $onSale = fake()->boolean();

        return [
            'name' => $this->faker->word(),
            'description' => '<p>' . $this->faker->paragraph() . '</p>',
            'price' => $price,
            'saleprice' => $this->faker->randomFloat(2, 100, $price),
            'show_rrp' => $onSale, // Add show_rrp
            'is_on_sale' => $onSale, // Add is_on_sale
            'stock' => $this->faker->numberBetween(0, 20),
            'is_featured' => $this->faker->boolean(10),
        ];
    }
}
