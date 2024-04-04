<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Field;
use App\Models\Manager;
use App\Models\Event;

class EventsSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $fieldIds = Field::pluck('id')->toArray();
        $managerIds = Manager::pluck('id')->toArray();
        $currentYear = date('Y');
        $startDate = $currentYear . '-01-01';
        $endDate = $currentYear . '-12-31';

        foreach (range(1, 3) as $index) { // Adjust the range as needed
            Event::create([
                'name' => $faker->unique()->word,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'event_date' => $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
                'field_id' => $faker->randomElement($fieldIds),
                'manager_id' => $faker->randomElement($managerIds),
            ]);
        }
    }
}
