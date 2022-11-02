<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $parentId = null;
        if (Category::count() > 0) {
            $childChance = $this->faker->boolean(90);
            if ($childChance) {
                $categoryIds = Category::pluck('id');
                $parentId = $this->faker->randomElement($categoryIds);
            }
        }
        return [
            'parent_id' => $parentId,
            'name' => $this->faker->word(),
        ];
    }
}
