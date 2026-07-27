<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AgeGroup>
 */
class AgeGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxAge = $this->faker->unique()->numberBetween(6, 18);

        return [
            'name' => (string) $maxAge,
            'min_age' => $maxAge - 1,
            'max_age' => $maxAge,
        ];
    }
}
