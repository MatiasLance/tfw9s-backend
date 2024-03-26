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

        foreach (range(1, 5) as $index) { // Adjust the range as needed
            Event::create([
                'name' => $faker->unique()->word,
                'description' => $faker->sentence,
                'event_date' => $faker->dateTime(),
                'field_id' => $faker->randomElement($fieldIds),
                'manager_id' => $faker->randomElement($managerIds),
            ]);
        }
    }
}
