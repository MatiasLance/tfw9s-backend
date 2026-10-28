<?php

namespace Database\Seeders;

use App\Models\ItemVariant;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Size and Color variant TYPES (these define the possible values)
        $sizeVariant = Variant::getSizeVariant();
        $colorVariant = Variant::getColorVariant();

        // 2. Create global (non-item-specific) color ItemVariants as predefined swatches
        // These are the color options available for ALL items that have color variants
        $colors = [
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
        ];

        $displayOrder = 0;
        foreach ($colors as $value => $hexcode) {
            ItemVariant::query()->updateOrCreate(
                [
                    'item_id' => null,
                    'type' => 'color',
                    'value' => $value,
                ],
                [
                    'variant_id' => $colorVariant->id,
                    'hexcode' => $hexcode,
                    'stock_quantity' => 0,  // Global swatches have 0 stock
                    'display_order' => $displayOrder++,
                    'is_active' => true,
                ]
            );
        }

        // 3. Also create global size ItemVariants as predefined options
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $displayOrder = 0;
        foreach ($sizes as $value) {
            ItemVariant::query()->updateOrCreate(
                [
                    'item_id' => null,
                    'type' => 'size',
                    'value' => $value,
                ],
                [
                    'variant_id' => $sizeVariant->id,
                    'hexcode' => null,
                    'stock_quantity' => 0,  // Global swatches have 0 stock
                    'display_order' => $displayOrder++,
                    'is_active' => true,
                ]
            );
        }
    }
}
