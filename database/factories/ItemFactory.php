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
        return [
            'name' => $this->faker->word(),
            'description' => '<p>' . $this->faker->paragraph() . '</p>',
            'price' => $this->faker->numberBetween(10, 300) * 10,
            'stock' => $this->faker->numberBetween(0, 20),
            'is_featured' => $this->faker->boolean(10),
        ];
    }
}
