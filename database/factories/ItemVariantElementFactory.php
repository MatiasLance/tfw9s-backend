<?php

namespace Database\Factories;

use App\Models\Element;
use App\Models\Item;
use App\Models\ItemVariantElement;
use App\Models\Media;
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
        $items = Item::pluck('id')->toArray();
        $elements = Element::pluck('id')->toArray();
        $overridePrice = $this->faker->boolean(10);
        $hasThumbnail = $this->faker->boolean(60);
        $thumbnailType = $this->faker->boolean() ? Variant::THUMBNAIL_TYPE_IMAGE : Variant::THUMBNAIL_TYPE_COLOR;

        return [
            'item_id' => $this->faker->randomElement($items),
            'element_id' => $this->faker->randomElement($elements),
            'price' => $overridePrice ? $this->faker->numberBetween(10, 300) * 10 : null,
            'stock' => $this->faker->numberBetween(0, 20),
            'thumbnail_type' => $hasThumbnail ? $thumbnailType : null,
            'thumbnail_color_value' => $hasThumbnail && $thumbnailType === Variant::THUMBNAIL_TYPE_COLOR ? $this->faker->hexColor : null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (ItemVariantElement $element) {
            if ($element->thumbnail_type === Variant::THUMBNAIL_TYPE_IMAGE) {
                $media = Media::factory()
                                ->placeholder()
                                ->create([
                                    'mediable_id' => $element->id,
                                    'mediable_type' => $element::class,
                                ]);
                $element->thumbnailImage()->save($media);
            }
        });
    }
}
