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
        $teamNames = [
            'Bulldogs',
            'Titans',
            'Ravagers',
            'Vikings',
            'Saints',
            'Panthers',
            'Dragons',
            'Sharks',
            'Tigers',
            'Storm',
            'Eagles',
            'Roosters',
            'Raiders',
            'Broncos',
            'Cowboys',
            'Warriors',
            'Rabbitohs',
            'Sea Eagles',
            'Knights',
            'Wests Tigers'
        ];
        
        return [
            'name' => $this->$faker->randomElement($teamNames),
            'description' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
            'field_id' => function () {
                return Field::factory()->create()->id;
            },
        ];
    }
}
