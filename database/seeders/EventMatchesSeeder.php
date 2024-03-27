<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Team;
use App\Models\Event;
use App\Models\EventMatch;

class EventMatchesSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $teamIds = Team::pluck('id')->toArray();
        $Events = Event::all();

        foreach ($Events as $event) {
            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'match_time' => $faker->time,
                'team1' => $faker->randomElement($teamIds),
                'team2' => $faker->randomElement($teamIds),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'match_time' => $faker->time,
                'team1' => $faker->randomElement($teamIds),
                'team2' => $faker->randomElement($teamIds),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'match_time' => $faker->time,
                'team1' => $faker->randomElement($teamIds),
                'team2' => $faker->randomElement($teamIds),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

    }
    }
}
