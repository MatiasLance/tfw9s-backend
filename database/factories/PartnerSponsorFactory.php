<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PartnerSponsor>
 */
class PartnerSponsorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'company_name' => $this->faker->word(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'description' => $this->faker->realText($maxNbChars = 200, $indexSize = 2),
        ];
    }
}
