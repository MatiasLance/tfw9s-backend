<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Region;
use App\Models\Manager;
use App\Models\AgeGroup;
use App\Models\Series;
use App\Models\Event;

class EventsSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $regionIds = Region::pluck('id')->toArray();
        $managerIds = Manager::pluck('id')->toArray();
        $agegroupIds = AgeGroup::pluck('id')->toArray();
        $seriesData = Series::all()->toArray();
        $currentYear = date('Y');
        $currentMonth = date('m');
        $startDate = $currentYear . '-'.$currentMonth.'-01';
        $endDate = $currentYear . '-'.$currentMonth.'-31';

        foreach ($agegroupIds as $agegroupId) {
            $eventType = $faker->randomElement(['Cup', 'League', 'Tournament', 'Championship']);
            $series = $faker->randomElement($seriesData);
            $eventName = $faker->unique()->state() ;
            $eventTitle = $eventName . ' ' . $eventType;
            Event::create([
                'name' => $eventTitle,
                'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                'event_date' => $faker->dateTimeBetween($series['start'], $series['end'])->format('Y-m-d'),
                'teamcount' => 4,
                'series_id' => $series['id'],
                'region_id' => $faker->randomElement($regionIds),
                'manager_id' => $faker->randomElement($managerIds),
                'agegroup_id' => $agegroupId,
            ]);
        }
    }
}
