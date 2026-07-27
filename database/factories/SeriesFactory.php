<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Series>
 */
class SeriesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+6 months');

        return [
            'name' => $this->faker->unique()->city().' Football Series',
            'type' => $this->faker->randomElement(['weekly', 'tournament', 'coast']),
            'description' => $this->faker->sentence(12),
            'address' => $this->faker->address(),
            'start' => $start->format('Y-m-d'),
            'end' => (clone $start)->modify('+12 weeks')->format('Y-m-d'),
            'price' => $this->faker->numberBetween(200, 600),
        ];
    }
}
