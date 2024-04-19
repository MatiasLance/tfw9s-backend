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
        $onSale = $this->faker->boolean();

        return [
            'name' => 'item ' . $this->faker->unique()->numberBetween(1, 100),
            'description' => '<p>' . 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Curabitur commodo est in quam feugiat tristique nec id urna. Duis eu neque tempor, aliquam nisl eget, dignissim dolor.
            Aenean finibus imperdiet porttitor.' . '</p>',
            'price' => $this->faker->randomFloat(2, 100, $price),
            'saleprice' => $price,
            'show_rrp' => $onSale, // Add show_rrp
            'is_on_sale' => $onSale, // Add is_on_sale
            'stock' => $this->faker->numberBetween(0, 20),
            'is_featured' => $this->faker->boolean(10),
        ];
    }
}

