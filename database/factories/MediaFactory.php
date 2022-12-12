<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'item_id' => 1,
            'hash' => $this->faker->sha256,
            'path' => 'media/items/' . hash('sha256', Carbon::now()->timestamp) . '.png',
            'format' => 'png',
            'mime_type' => 'image/png',
            'size' => $this->faker->numberBetween(300, 50000),
        ];
    }
}
