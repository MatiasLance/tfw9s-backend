<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Field;
use App\Models\Manager;
use App\Models\AgeGroup;
use App\Models\Event;

class EventsSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $fieldIds = Field::pluck('id')->toArray();
        $managerIds = Manager::pluck('id')->toArray();
        $agegroupIds = AgeGroup::pluck('id')->toArray();
        $currentYear = date('Y');
        $currentMonth = date('m');
        $startDate = $currentYear . '-'.$currentMonth.'-01';
        $endDate = $currentYear . '-'.$currentMonth.'-31';

        foreach ($agegroupIds as $agegroupId) {
            $eventType = $faker->randomElement(['Cup', 'League', 'Tournament', 'Championship']);
            $eventName = $faker->unique()->state() ;
            $eventTitle = $eventName . ' ' . $eventType;
            Event::create([
                'name' => $eventTitle,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'event_date' => $faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
                'field_id' => $faker->randomElement($fieldIds),
                'manager_id' => $faker->randomElement($managerIds),
                'agegroup_id' => $agegroupId,
            ]);
        }
    }
}
