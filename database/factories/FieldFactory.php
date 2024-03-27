<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Region;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Field>
 */
class FieldFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->city() ,
            'description' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
            'region_id' => function () {
                return Region::factory()->create()->id;
            },
        ];
    }
}

