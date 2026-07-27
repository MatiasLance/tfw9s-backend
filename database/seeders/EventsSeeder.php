<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Event;
use App\Models\Region;
use App\Models\Series;
use Illuminate\Database\Seeder;
use RuntimeException;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ageGroups = AgeGroup::query()->orderBy('id')->get();
        $regionIds = Region::query()->orderBy('id')->pluck('id');
        $series = Series::query()->orderBy('id')->get();

        if ($ageGroups->isEmpty() || $regionIds->isEmpty() || $series->isEmpty()) {
            throw new RuntimeException(
                'EventsSeeder requires age groups, regions, and series to be seeded first.'
            );
        }

        foreach ($ageGroups as $index => $ageGroup) {
            $selectedSeries = $series[$index % $series->count()];
            $event = Event::query()->firstOrNew([
                'round' => 'round',
                'agegroup_id' => $ageGroup->id,
            ]);

            $event->forceFill([
                'time' => fake()->time(),
                'round' => 'round',
                'event_date' => $selectedSeries->start,
                'teamcount' => 4,
                'region_id' => $regionIds[$index % $regionIds->count()],
                'agegroup_id' => $ageGroup->id,
                'series_id' => $selectedSeries->id,
            ])->save();
        }
    }
}
