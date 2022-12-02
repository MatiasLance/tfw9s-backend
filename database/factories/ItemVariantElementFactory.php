<?php

namespace Database\Factories;

use App\Modules\Item\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemVariantElement>
 */
class ItemVariantElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $overridePrice = $this->faker->boolean(10);
        $hasThumbnail = $this->faker->boolean(60);
        $thumbnailType = $this->faker->boolean() ? Variant::THUMBNAIL_TYPE_IMAGE : Variant::THUMBNAIL_TYPE_COLOR;

        return [
            'price' => $overridePrice ? $this->faker->numberBetween(10, 300) * 10 : null,
            'stock' => $this->faker->numberBetween(0, 20),
            'thumbnail_type' => $hasThumbnail ? $thumbnailType : null,
            'thumbnail_color_value' => $hasThumbnail && $thumbnailType === Variant::THUMBNAIL_TYPE_COLOR ? $this->faker->hexColor : null,
        ];
    }
}
