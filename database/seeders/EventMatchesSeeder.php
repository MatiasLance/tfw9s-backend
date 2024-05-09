<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Field;
use App\Models\Team;
use App\Models\Event;
use App\Models\EventMatch;

class EventMatchesSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $Events = Event::all();

        foreach ($Events as $event) {
            $teams = Team::where('event_id', $event->id)->pluck('id')->toArray();
            $fields = Field::where('region_id', $event->region_id)->pluck('id')->toArray();
            $teamsCount = count($teams);
            
            // Shuffle teamIds randomly
            shuffle($teams);
            
            // Take the first 3 teamIds
            $team1 = $teams[0];
            $team2 = $teams[1];
            $team3 = $teams[2];
            
            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'field_id' => $faker->randomElement($fields),
                'match_time' => $faker->time,
                'team1' => $team1,
                'team2' => $team2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'field_id' => $faker->randomElement($fields),
                'match_time' => $faker->time,
                'team1' => $team2,
                'team2' => $team3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_matches')->insert([
                'event_id' => $event->id,
                'field_id' => $faker->randomElement($fields),
                'match_time' => $faker->time,
                'team1' => $team3,
                'team2' => $team1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}