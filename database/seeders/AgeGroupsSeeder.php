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
            ['name' => 'U6', 'min_age' => 5, 'max_age' => 6],
            ['name' => 'U7', 'min_age' => 6, 'max_age' => 7],
            ['name' => 'U8', 'min_age' => 7, 'max_age' => 8],
            ['name' => 'U9', 'min_age' => 8, 'max_age' => 9],
            ['name' => 'U10', 'min_age' => 9, 'max_age' => 10],
            ['name' => 'U11', 'min_age' => 10, 'max_age' => 11],
            ['name' => 'U12', 'min_age' => 11, 'max_age' => 12],
        ];

        foreach ($ageGroups as $ageGroup) {
            AgeGroup::create($ageGroup);
        }
    }
}
