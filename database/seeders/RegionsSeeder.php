<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regionNames = [
            'Central Coast',
            'Eastern Suburbs',
            'Greater Western Sydney',
            'Hills District',
            'Hunter Valley',
            'Illawarra',
            'Inner West',
            'Mid North Coast',
            'Northern Beaches',
            'Northern Rivers',
            'Riverina',
            'South Coast',
            'Southern Highlands',
            'Sydney CBD',
            'Western Plains',
        ];

        foreach ($regionNames as $name) {
            $attributes = Region::factory()
                ->make(['name' => $name])
                ->getAttributes();

            $region = Region::withTrashed()->firstOrNew(['name' => $name]);
            $region->forceFill($attributes);

            if ($region->trashed()) {
                $region->restore();
            }

            $region->save();
        }
    }
}
