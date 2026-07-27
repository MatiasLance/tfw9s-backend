<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Series;
use App\Models\TeamLimit;
use Illuminate\Database\Seeder;

class TeamLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ageGroups = AgeGroup::query()->get();

        Series::query()->each(function (Series $series) use ($ageGroups): void {
            foreach ($ageGroups as $ageGroup) {
                $teamLimit = TeamLimit::query()
                    ->where('series_id', $series->id)
                    ->whereHas('ageGroups', function ($query) use ($ageGroup): void {
                        $query->where('age_groups.id', $ageGroup->id);
                    })
                    ->first();

                if (! $teamLimit) {
                    $teamLimit = new TeamLimit();
                    $teamLimit->series_id = $series->id;
                    $teamLimit->save();
                    $teamLimit->ageGroups()->attach($ageGroup->id);
                }

                $teamLimit->team_limit = 8;
                $teamLimit->save();
            }
        });
    }
}
