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
            
        // Shuffle teamIds randomly
        shuffle($teamIds);
        
        // Take the first 3 teamIds
        $team1 = $teamIds[0];
        $team2 = $teamIds[1];
        $team3 = $teamIds[2];
        
            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'match_time' => $faker->time,
                'team1' => $team1,
                'team2' => $team2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'match_time' => $faker->time,
                'team1' => $team2,
                'team2' => $team3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'match_time' => $faker->time,
                'team1' => $team3,
                'team2' => $team1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

    }
    }
}
