<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Field;
use App\Models\Region;

class FieldsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $regions = Region::all();
        $fieldsPerRegion = 3; // Number of fields to assign per region

        foreach ($regions as $region) {
            // Get a subset of field names for this region
            $regionFields = $this->getRegionFields($fieldsPerRegion);

            // Create fields for this region
            foreach ($regionFields as $fieldName) {
                Field::create([
                    'name' => $fieldName,
                    'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'region_id' => $region->id,
                ]);
            }
        }
    }

    /**
     * Get a subset of field names for a region.
     *
     * @param int $count
     * @return array
     */
    private function getRegionFields($count)
    {
        // Array of all field names
        $fields = [
            'Rugby Park',
            'Scrum Grounds',
            'Tryline Stadium',
            'Lineout Field',
            'Kickoff Grounds',
            'Conversion Arena',
            'Ruck Field',
            'Maul Grounds',
            'Breakdown Stadium',
            'Tackle Zone',
            'Grubber Park',
            'Offload Grounds',
            'Passing Arena',
            'Tighthead Field',
            'Loosehead Grounds'
        ];

        // Shuffle the field names and take a subset of $count
        shuffle($fields);
        return array_slice($fields, 0, $count);
    }
}