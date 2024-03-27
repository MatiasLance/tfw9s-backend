<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Field;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->streetName(),
            'description' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
            'field_id' => function () {
                return Field::factory()->create()->id;
            },
        ];
    }
}
