<?php

namespace Database\Seeders;

use App\Models\ItemVariant;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            'Red' => '#FF0000',
            'Blue' => '#0000FF',
            'Green' => '#008000',
            'Yellow' => '#FFFF00',
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Purple' => '#800080',
            'Orange' => '#FFA500',
        ];

        foreach ($colors as $value => $hexcode) {
            ItemVariant::query()->updateOrCreate(
                ['type' => 'color', 'value' => $value],
                [
                    'hexcode' => $hexcode,
                    'stock_quantity' => 100,
                    'is_active' => true,
                ]
            );
        }
    }
}
