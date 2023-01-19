<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ToggleMasterShippingSetting>
 */
class ToggleMasterShippingSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'togglemastersetting1' => fake()->boolean(),
            'togglemastersetting2' => fake()->boolean()
        ];
    }
}
