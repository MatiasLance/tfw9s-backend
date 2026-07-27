<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Region;
use App\Models\Series;
use App\Models\Team;
use Illuminate\Database\Seeder;
use RuntimeException;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            'Avoca',
            'Bateau Bay',
            'Erina',
            'Gosford',
            'Kincumber',
            'Lisarow',
            'Terrigal',
            'The Entrance',
            'Toukley',
            'Wyong',
        ];
        $mascots = ['Blazers', 'Falcons', 'Rangers', 'Strikers', 'United'];
        $teamNames = [];

        foreach ($locations as $location) {
            foreach ($mascots as $mascot) {
                $teamNames[] = "{$location} {$mascot}";
            }
        }

        $regions = Region::query()->pluck('id');
        $ageGroups = AgeGroup::query()->pluck('id');
        $series = Series::query()->pluck('id');

        if ($regions->isEmpty() || $ageGroups->isEmpty() || $series->isEmpty()) {
            throw new RuntimeException(
                'TeamsSeeder requires regions, age groups, and series to be seeded first.'
            );
        }

        foreach ($teamNames as $index => $name) {
            $eventIndex = intdiv($index, 3) % $ageGroups->count();

            $attributes = Team::factory()
                ->make([
                    'name' => $name,
                    'region_id' => $regions[$eventIndex % $regions->count()],
                    'agegroup_id' => $ageGroups[$eventIndex],
                    'series_id' => $series[$eventIndex % $series->count()],
                ])
                ->getAttributes();

            $team = Team::withTrashed()->firstOrNew(['name' => $name]);
            $team->forceFill($attributes);

            if ($team->trashed()) {
                $team->restore();
            }

            $team->save();
        }
    }
}
