<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Player $player): void {
            if ($player->agegroup_id && $player->series_id) {
                return;
            }

            $team = Team::query()->find($player->team_id);

            if ($team) {
                $player->agegroup_id = $team->agegroup_id;
                $player->series_id = $team->series_id;
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'contact_firstname' => $this->faker->firstName(),
            'contact_lastname' => $this->faker->lastName(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'player_firstname' => $this->faker->firstName(),
            'player_lastname' => $this->faker->lastName(),
            'dob' => $this->faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Make the player belong to a team and inherit its series and age group.
     */
    public function forTeam(Team $team): static
    {
        $minimumAge = $team->agegroup->min_age;
        $maximumAge = $team->agegroup->max_age;

        return $this->state(fn () => [
            'team_id' => $team->id,
            'agegroup_id' => $team->agegroup_id,
            'series_id' => $team->series_id,
            'dob' => $this->faker->dateTimeBetween(
                "-{$maximumAge} years",
                "-{$minimumAge} years"
            )->format('Y-m-d'),
        ]);
    }
}
