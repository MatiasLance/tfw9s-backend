<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guideline>
 */
class GuidelineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'type' => $this->faker->randomElement(['Code_of_conduct', 'Rules', 'Insurance']),
            'content' => '<p>' . $this->faker->realText($maxNbChars = 200, $indexSize = 2) . '</p>',
        ];
    }
}
