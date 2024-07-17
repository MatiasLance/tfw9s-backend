<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Region;

class RegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $regions = [
            'Northern Lakes International',
            'Northern lakes mini ',
            'Southern Corridor ',
            'Western Sydney International ',
            'Western Sydney Mini '
        ];
        

        foreach ($regions as $region) {
            Region::create([
                'name' => $region,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
            ]);
        }
    }
}
