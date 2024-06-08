<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Series;
use App\Models\AgeGroup;
use App\Models\TeamLimit;

class TeamLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seriesList = Series::all();
        $ageGroups = AgeGroup::all();

        foreach ($seriesList as $series) {
            foreach ($ageGroups as $ageGroup) {
                $teamLimit = new TeamLimit();
                $teamLimit->series_id = $series->id;
                $teamLimit->team_limit = 8;
                $teamLimit->save();

                $teamLimit->ageGroups()->attach($ageGroup->id);
            }
        }
    }
}

