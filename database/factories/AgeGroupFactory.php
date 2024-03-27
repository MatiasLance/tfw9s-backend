<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Field>
 */
class AgeGroupFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
public function definition()
    {
        $minAge = $this->faker->numberBetween(3, 25);
        $maxAge = $this->faker->numberBetween($minAge + 4, 30);

        return [
            'name' => $this->faker->unique()->numerify('ageGroup-####'),
            'min_age' => $minAge,
            'max_age' => $maxAge,
        ];
    }
}

