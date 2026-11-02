<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemVariant;
use App\Models\Tag;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ensure Size and Color variant types exist first
        $sizeVariant = Variant::getSizeVariant();
        $colorVariant = Variant::getColorVariant();

        // Create items with size/color variants
        Item::factory()
            ->count(10)
            ->has(Tag::factory()->count(2))
            ->has(Category::factory())
            ->create()
            ->each(function (Item $item) use ($sizeVariant, $colorVariant) {
                // 40% of items have size variants
                if (fake()->boolean(40)) {
                    $this->createSizeVariants($item, $sizeVariant);
                }

                // 50% of items have color variants
                if (fake()->boolean(50)) {
                    $this->createColorVariants($item, $colorVariant);
                }
            });
    }

    /**
     * Create size variants for an item.
     */
    private function createSizeVariants(Item $item, Variant $variant): void
    {
        $allSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        // Pick 2 to 5 random sizes
        $selectedSizes = fake()->randomElements($allSizes, fake()->numberBetween(2, 5));

        $displayOrder = 0;
        foreach ($selectedSizes as $size) {
            // Random price override (30% chance, up to ±30% of item price)
            $priceOverride = null;
            if (fake()->boolean(30)) {
                $variance = (fake()->randomFloat(2, -0.30, 0.30));
                $priceOverride = max(0, (int) round($item->centPrice() * (1 + $variance)));
            }

            ItemVariant::factory()
                ->forItem($item)
                ->size($size)
                ->create([
                    'variant_id' => $variant->id,
                    'display_order' => $displayOrder++,
                    'price_override' => $priceOverride,
                    'stock_quantity' => fake()->numberBetween(0, 20),
                    'is_active' => true,
                ]);
        }
    }

    /**
     * Create color variants for an item.
     */
    private function createColorVariants(Item $item, Variant $variant): void
    {
        $colorOptions = [
            'Red' => '#FF0000',
            'Blue' => '#0000FF',
            'Green' => '#008000',
            'Yellow' => '#FFFF00',
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Purple' => '#800080',
            'Orange' => '#FFA500',
            'Pink' => '#FFC0CB',
            'Navy' => '#000080',
            'Grey' => '#808080',
            'Brown' => '#A52A2A',
        ];

        // Pick 2 to 6 random colors
        $selectedColors = fake()->randomElements(
            array_keys($colorOptions),
            fake()->numberBetween(2, min(6, count($colorOptions)))
        );

        $displayOrder = 0;
        foreach ($selectedColors as $colorName) {
            // Random price override (20% chance, up to ±20% of item price)
            $priceOverride = null;
            if (fake()->boolean(20)) {
                $variance = (fake()->randomFloat(2, -0.20, 0.20));
                $priceOverride = max(0, (int) round($item->centPrice() * (1 + $variance)));
            }

            ItemVariant::factory()
                ->forItem($item)
                ->color($colorName, $colorOptions[$colorName])
                ->create([
                    'variant_id' => $variant->id,
                    'display_order' => $displayOrder++,
                    'price_override' => $priceOverride,
                    'stock_quantity' => fake()->numberBetween(0, 20),
                    'is_active' => true,
                ]);
        }
    }
}
