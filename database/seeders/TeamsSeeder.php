<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Team;
use App\Models\Field;
use App\Models\AgeGroup;

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
        $agegroupIds = AgeGroup::pluck('id')->toArray();

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
        

        $teamsPerAgeGroup = ceil(count($teams) / count($agegroupIds));
        
        foreach ($agegroupIds as $index => $agegroupId) {
            $startIndex = $index * $teamsPerAgeGroup;
            $selectedTeams = array_slice($teams, $startIndex, $teamsPerAgeGroup);
            
            foreach ($selectedTeams as $selectedTeam) {
                Team::create([
                    'name' => $selectedTeam,
                    'description' => $faker->realText($maxNbChars = 200, $indexSize = 2),
                    'field_id' => $faker->randomElement($fieldIds),
                    'agegroup_id' => $agegroupId,
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
