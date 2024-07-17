<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\AgeGroup;

class AgeGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ageGroups = [
            ['name' => 'Under 6', 'min_age' => 5, 'max_age' => 6],
            ['name' => 'Under 7', 'min_age' => 6, 'max_age' => 7],
            ['name' => 'Under 8', 'min_age' => 7, 'max_age' => 8],
            ['name' => 'Under 9', 'min_age' => 8, 'max_age' => 9],
            ['name' => 'Under 10', 'min_age' => 9, 'max_age' => 10],
            ['name' => 'Under 11', 'min_age' => 10, 'max_age' => 11],
            ['name' => 'Under 12', 'min_age' => 11, 'max_age' => 12],
            ['name' => 'Under 13', 'min_age' => 12, 'max_age' => 13],
            ['name' => 'Under 14', 'min_age' => 13, 'max_age' => 14],
            ['name' => 'Under 15', 'min_age' => 14, 'max_age' => 15],
            ['name' => 'Under 16', 'min_age' => 15, 'max_age' => 16],
            ['name' => 'Under 17', 'min_age' => 16, 'max_age' => 17],
            ['name' => 'Under 18', 'min_age' => 17, 'max_age' => 18],
        ];

        foreach ($ageGroups as $ageGroup) {
            AgeGroup::create($ageGroup);
        }
    }
}
