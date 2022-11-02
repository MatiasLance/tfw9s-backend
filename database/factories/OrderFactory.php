<?php

namespace Database\Factories;

use App\Modules\Order\ShippingType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $shippingTypes = [
            ShippingType::DELIVERY,
            ShippingType::PICKUP,
        ];

        return [
            'payment_intent_id' => $this->faker->bothify('pi_*****************'),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'shipping_type' => $this->faker->randomElement($shippingTypes),
            'address' => $this->faker->address(),
            'post_code' => $this->faker->postcode(),
            'remarks' => $this->faker->paragraph(),
            'total' => $this->faker->numberBetween(10, 1250),
        ];
    }
}
