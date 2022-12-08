<?php

namespace Database\Factories;

use App\Models\Element;
use App\Models\Media;
use App\Modules\Item\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Element>
 */
class ElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $hasThumbnail = $this->faker->boolean(60);
        $thumbnailType = $this->faker->boolean() ? Variant::THUMBNAIL_TYPE_IMAGE : Variant::THUMBNAIL_TYPE_COLOR;

        return [
            'name' => $this->faker->word(),
            'thumbnail_type' => $hasThumbnail ? $thumbnailType : null,
            'thumbnail_color_value' => $hasThumbnail && $thumbnailType === Variant::THUMBNAIL_TYPE_COLOR ? $this->faker->hexColor() : null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Element $element) {
            if ($element->thumbnail_type === Variant::THUMBNAIL_TYPE_IMAGE) {
                $media = Media::factory()->create([
                    'mediable_id' => $element->id,
                    'mediable_type' => $element::class,
                ]);
                $element->thumbnailImage()->save($media);
            }
        });
    }
}
