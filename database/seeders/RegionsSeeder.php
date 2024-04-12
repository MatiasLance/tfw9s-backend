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
            'New Zealand',
            'Australia',
            'South Africa',
            'Scotland',
            'Ireland',
            'France',
            'Argentina',
            'Japan',
            'Italy',
            'Samoa',
            'Canada',
            'United States',
            'Uruguay',
            'Spain',
            'Romania'
        ];
        

        foreach ($regions as $region) {
            Region::create([
                'name' => $region,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
            ]);
        }
    }
}
