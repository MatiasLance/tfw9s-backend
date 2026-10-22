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
     * Event definitions shared with EventMatchesSeeder.
     *
     * Dates are relative to the current week so the dataset always contains
     * past, current, and upcoming events. Re-running the seeder updates the
     * existing event for each series/age-group/round/date rather than
     * duplicating it; weekly rounds share the "round" stage value and are
     * distinguished by their event date.
     *
     * @return array<int, array{key: string, series: string, agegroup: string, region: string, round: string, time: string, teamcount: int, week_offset: int, day_offset: int}>
     */
    public static function definitions(): array
    {
        return [
            [
                'key' => 'u9-round-1',
                'series' => 'Weekly Series',
                'agegroup' => '9',
                'region' => 'Central Coast',
                'round' => 'round',
                'time' => '18:00',
                'teamcount' => 8,
                'week_offset' => -3,
                'day_offset' => 5,
            ],
            [
                'key' => 'u9-round-2',
                'series' => 'Weekly Series',
                'agegroup' => '9',
                'region' => 'Central Coast',
                'round' => 'round',
                'time' => '18:00',
                'teamcount' => 8,
                'week_offset' => -2,
                'day_offset' => 5,
            ],
            [
                'key' => 'u9-round-3',
                'series' => 'Weekly Series',
                'agegroup' => '9',
                'region' => 'Central Coast',
                'round' => 'round',
                'time' => '18:30',
                'teamcount' => 8,
                'week_offset' => 1,
                'day_offset' => 5,
            ],
            [
                'key' => 'u12-round-1',
                'series' => 'Central Coast',
                'agegroup' => '12',
                'region' => 'Central Coast',
                'round' => 'round',
                'time' => '09:00',
                'teamcount' => 8,
                'week_offset' => -2,
                'day_offset' => 6,
            ],
            [
                'key' => 'u12-round-2',
                'series' => 'Central Coast',
                'agegroup' => '12',
                'region' => 'Central Coast',
                'round' => 'round',
                'time' => '09:00',
                'teamcount' => 8,
                'week_offset' => -1,
                'day_offset' => 6,
            ],
            [
                'key' => 'u12-bye-only',
                'series' => 'Central Coast',
                'agegroup' => '12',
                'region' => 'Central Coast',
                'round' => 'round',
                'time' => '10:30',
                'teamcount' => 4,
                'week_offset' => 1,
                'day_offset' => 6,
            ],
            [
                'key' => 'open-round-1',
                'series' => 'Tournament',
                'agegroup' => 'Open',
                'region' => 'Hunter Valley',
                'round' => 'round',
                'time' => '13:00',
                'teamcount' => 8,
                'week_offset' => -4,
                'day_offset' => 6,
            ],
            [
                'key' => 'open-pool-a-round',
                'series' => 'Tournament',
                'agegroup' => 'Open',
                'region' => 'Hunter Valley',
                'round' => 'pool_a_round',
                'time' => '15:00',
                'teamcount' => 4,
                'week_offset' => -2,
                'day_offset' => 6,
            ],
            [
                'key' => 'open-semi',
                'series' => 'Tournament',
                'agegroup' => 'Open',
                'region' => 'Hunter Valley',
                'round' => 'semi',
                'time' => '19:00',
                'teamcount' => 4,
                'week_offset' => 0,
                'day_offset' => 3,
            ],
            [
                'key' => 'open-final',
                'series' => 'Tournament',
                'agegroup' => 'Open',
                'region' => 'Hunter Valley',
                'round' => 'final',
                'time' => '16:00',
                'teamcount' => 2,
                'week_offset' => 3,
                'day_offset' => 6,
            ],
        ];
    }

    /**
     * Resolve the event date for a definition relative to the current week.
     *
     * @param  array<string, mixed>  $definition
     */
    public static function eventDate(array $definition): string
    {
        return now()->startOfWeek()
            ->addWeeks($definition['week_offset'])
            ->addDays($definition['day_offset'])
            ->toDateString();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::definitions() as $definition) {
            $series = Series::withTrashed()->where('name', $definition['series'])->first();
            $ageGroup = AgeGroup::withTrashed()->where('name', $definition['agegroup'])->first();
            $region = Region::withTrashed()->where('name', $definition['region'])->first();

            if (! $series || ! $ageGroup || ! $region) {
                throw new RuntimeException(
                    "EventsSeeder cannot resolve the series, age group, or region for {$definition['key']}."
                );
            }

            $eventDate = self::eventDate($definition);

            $event = Event::withTrashed()
                ->where('series_id', $series->id)
                ->where('agegroup_id', $ageGroup->id)
                ->where('round', $definition['round'])
                ->where('event_date', $eventDate)
                ->first() ?? new Event();

            $event->forceFill([
                'time' => $definition['time'],
                'round' => $definition['round'],
                'event_date' => $eventDate,
                'teamcount' => $definition['teamcount'],
                'region_id' => $region->id,
                'agegroup_id' => $ageGroup->id,
                'series_id' => $series->id,
            ]);

            if ($event->trashed()) {
                $event->restore();
            }

            $event->save();
        }
    }
}
