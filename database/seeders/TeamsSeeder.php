<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Team;
use App\Models\Field;
use App\Models\Event;
use App\Models\AgeGroup;
use App\Models\Series;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $fieldIds = Field::pluck('id')->toArray();
        $eventIds = Event::pluck('id')->toArray();
        $agegroupIds = AgeGroup::pluck('id')->toArray();
        $seriesIds = Series::pluck('id')->toArray();

        $teams = [
            'Bulldogs',
            'Titans',
            'Ravagers',
            'Vikings',
            'Saints',
            'Panthers',
            'Dragons',
            'Sharks',
            'Tigers',
            'Storm',
            'Eagles',
            'Roosters',
            'Raiders',
            'Broncos',
            'Cowboys',
            'Warriors',
            'Rabbitohs',
            'Sea Eagles',
            'Knights',
            'Wests Tigers',
            'Hawks'
        ];


        $teamsPerEvent = ceil(count($teams) / count($eventIds));

        foreach ($eventIds as $index => $id) {
            $startIndex = $index * $teamsPerEvent;
            $selectedTeams = array_slice($teams, $startIndex, $teamsPerEvent);

            foreach ($selectedTeams as $selectedTeam) {
                Team::create([
                    'name' => $selectedTeam,
                    // 'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    // 'field_id' => $faker->randomElement($fieldIds),
                    // 'event_id' => $id,
                    'series_id' => $faker->randomElement($seriesIds),
                    'agegroup_id' => $faker->randomElement($fieldIds),
                    'coach_name' => $faker->name,
                    'coach_mobile' => $faker->unique()->phoneNumber,
                    'coach_email' => $faker->unique()->safeEmail,
                    'manager_name' => $faker->name,
                    'manager_mobile' => $faker->unique()->phoneNumber,
                    'manager_email' => $faker->unique()->safeEmail,
                ]);
            }
        }
    }
}
