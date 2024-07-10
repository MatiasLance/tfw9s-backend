<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\HomePageInformation;

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
            'imageable_type' => HomePageInformation::class,
            'imageable_id' => HomePageInformation::first()->id,
            'hash' => $this->faker->sha256,
            'path' => 'media/default/kidsplaying.jpg',
            'format' => 'jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(300, 50000),
        ];
    }
}
