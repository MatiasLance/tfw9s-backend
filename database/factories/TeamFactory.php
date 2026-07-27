<?php

namespace Database\Factories;

use App\Models\AgeGroup;
use App\Models\Region;
use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    public function definition(): array
    {
        $mascots = [
            'Blazers',
            'Comets',
            'Falcons',
            'Rangers',
            'Sharks',
            'Storm',
            'Strikers',
            'Titans',
            'United',
            'Wanderers',
        ];

        return [
            'name' => $this->faker->unique()->city().' '.$this->faker->randomElement($mascots),
            'agegroup_id' => AgeGroup::factory(),
            'series_id' => Series::factory(),
            'region_id' => Region::factory(),
            'coach_name' => $this->faker->name(),
            'coach_mobile' => $this->faker->phoneNumber(),
            'coach_email' => $this->faker->unique()->safeEmail(),
            'manager_name' => $this->faker->name(),
            'manager_mobile' => $this->faker->phoneNumber(),
            'manager_email' => $this->faker->unique()->safeEmail(),
            'player_limit' => 50,
            'pool' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
