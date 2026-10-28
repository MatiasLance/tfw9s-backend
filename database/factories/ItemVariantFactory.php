<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Variant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemVariant>
 */
class ItemVariantFactory extends Factory
{
    protected $model = ItemVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'variant_id' => null,
            'type' => 'size',
            'value' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'hexcode' => null,
            'image_path' => null,
            'use_image' => false,
            'sku' => strtoupper(fake()->lexify('????-????')),
            'price_override' => null,
            'stock_quantity' => fake()->numberBetween(0, 20),
            'display_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that this is a size variant.
     */
    public function size(?string $value = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'size',
            'value' => $value ?? fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'hexcode' => null,
            'use_image' => false,
        ]);
    }

    /**
     * Indicate that this is a color variant.
     */
    public function color(?string $value = null, ?string $hexcode = null): static
    {
        $color = $value ?? fake()->randomElement(['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange']);
        $codes = [
            'Red' => '#FF0000',
            'Blue' => '#0000FF',
            'Green' => '#008000',
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Yellow' => '#FFFF00',
            'Purple' => '#800080',
            'Orange' => '#FFA500',
        ];

        return $this->state(fn (array $attributes) => [
            'type' => 'color',
            'value' => $color,
            'hexcode' => $hexcode ?? $codes[$color] ?? fake()->hexColor(),
            'use_image' => false,
        ]);
    }

    /**
     * Attach to a specific item.
     */
    public function forItem(Item $item): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => $item->id,
        ]);
    }

    /**
     * Link to a specific Variant type.
     */
    public function ofVariantType(Variant $variant): static
    {
        return $this->state(fn (array $attributes) => [
            'variant_id' => $variant->id,
            'type' => $variant->type,
        ]);
    }

    /**
     * Mark as out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Mark as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
