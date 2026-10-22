<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Event;
use App\Models\EventMatch;
use App\Models\Field;
use App\Models\Series;
use App\Models\Team;
use Illuminate\Database\Seeder;
use RuntimeException;

class EventMatchesSeeder extends Seeder
{
    /**
     * Fixtures for each seeded event.
     *
     * Team indexes refer to the demo teams for that event's age group sorted
     * by name. A fixture with "bye" => true is stored as team2 = 0, which is
     * the bye sentinel used by the application.
     */
    private const FIXTURES = [
        'u9-round-1' => [
            ['team1' => 0, 'team2' => 1, 'status' => 'completed', 'score' => [18, 10]],
            ['team1' => 2, 'team2' => 3, 'status' => 'draw', 'score' => [6, 6]],
            ['team1' => 4, 'team2' => 5, 'status' => 'completed', 'score' => [14, 22]],
            ['team1' => 6, 'team2' => 7, 'status' => 'unplayed'],
        ],
        'u9-round-2' => [
            ['team1' => 0, 'team2' => 2, 'status' => 'completed', 'score' => [20, 12]],
            ['team1' => 1, 'team2' => 3, 'status' => 'draw', 'score' => [10, 10]],
            ['team1' => 4, 'team2' => 6, 'status' => 'unplayed'],
            ['team1' => 5, 'team2' => 7, 'status' => 'unplayed'],
        ],
        'u9-round-3' => [
            ['team1' => 0, 'team2' => 3, 'status' => 'unplayed'],
            ['team1' => 1, 'team2' => 2, 'status' => 'unplayed'],
            ['team1' => 4, 'team2' => 7, 'status' => 'unplayed'],
            ['team1' => 5, 'team2' => 6, 'status' => 'unplayed'],
        ],
        'u12-round-1' => [
            ['team1' => 0, 'team2' => 1, 'status' => 'completed', 'score' => [24, 12]],
            ['team1' => 2, 'team2' => 3, 'status' => 'completed', 'score' => [6, 18]],
            ['team1' => 4, 'team2' => 5, 'status' => 'draw', 'score' => [30, 30]],
            ['team1' => 6, 'bye' => true],
            ['team1' => 7, 'bye' => true],
        ],
        'u12-round-2' => [
            ['team1' => 0, 'team2' => 4, 'status' => 'completed', 'score' => [16, 8]],
            ['team1' => 1, 'team2' => 5, 'status' => 'completed', 'score' => [10, 4]],
            ['team1' => 2, 'team2' => 6, 'status' => 'unplayed'],
            ['team1' => 3, 'bye' => true],
            ['team1' => 7, 'bye' => true],
        ],
        'u12-bye-only' => [
            ['team1' => 0, 'bye' => true],
            ['team1' => 1, 'bye' => true],
            ['team1' => 2, 'bye' => true],
            ['team1' => 3, 'bye' => true],
        ],
        'open-round-1' => [
            ['team1' => 0, 'team2' => 1, 'status' => 'completed', 'score' => [12, 6]],
            ['team1' => 2, 'team2' => 3, 'status' => 'draw', 'score' => [14, 14]],
            ['team1' => 4, 'team2' => 5, 'status' => 'abandoned', 'score' => [0, 0]],
            ['team1' => 6, 'team2' => 7, 'status' => 'completed', 'score' => [22, 10]],
        ],
        'open-pool-a-round' => [
            ['team1' => 0, 'team2' => 2, 'status' => 'completed', 'score' => [18, 16]],
            ['team1' => 1, 'team2' => 3, 'status' => 'completed', 'score' => [8, 20]],
        ],
        'open-semi' => [
            ['team1' => 0, 'team2' => 4, 'status' => 'completed', 'score' => [22, 14]],
            ['team1' => 1, 'team2' => 5, 'status' => 'unplayed'],
        ],
        'open-final' => [
            ['team1' => 0, 'team2' => 1, 'status' => 'unplayed'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fallbackFieldId = Field::query()->orderBy('id')->value('id');

        if (! $fallbackFieldId) {
            throw new RuntimeException('EventMatchesSeeder requires at least one field.');
        }

        foreach (EventsSeeder::definitions() as $definition) {
            $series = Series::withTrashed()->where('name', $definition['series'])->first();
            $ageGroup = AgeGroup::withTrashed()->where('name', $definition['agegroup'])->first();

            if (! $series || ! $ageGroup) {
                throw new RuntimeException(
                    "EventMatchesSeeder cannot resolve the series or age group for {$definition['key']}."
                );
            }

            $event = Event::query()
                ->where('series_id', $series->id)
                ->where('agegroup_id', $ageGroup->id)
                ->where('round', $definition['round'])
                ->where('event_date', EventsSeeder::eventDate($definition))
                ->first();

            if (! $event) {
                throw new RuntimeException(
                    "EventMatchesSeeder requires the {$definition['key']} event to be seeded first."
                );
            }

            $teamNames = TeamsSeeder::definitions()[$definition['agegroup']]['teams'];
            $teamIds = Team::query()
                ->whereIn('name', $teamNames)
                ->orderBy('name')
                ->pluck('id')
                ->values();

            if ($teamIds->count() !== count($teamNames)) {
                throw new RuntimeException(
                    "EventMatchesSeeder requires all {$definition['agegroup']} demo teams to be seeded first."
                );
            }

            $fieldId = Field::query()
                ->where('region_id', $event->region_id)
                ->value('id') ?? $fallbackFieldId;

            $expectedMatchIds = [];

            foreach (self::FIXTURES[$definition['key']] as $fixture) {
                $isBye = ! empty($fixture['bye']);
                $team1Id = $teamIds[$fixture['team1']];
                $team2Id = $isBye ? 0 : $teamIds[$fixture['team2']];

                $attributes = $this->fixtureAttributes($fixture, $team1Id, $team2Id, $fieldId);

                $match = EventMatch::withTrashed()
                    ->where('event_id', $event->id)
                    ->where('team1', $team1Id)
                    ->where('team2', $team2Id)
                    ->first() ?? new EventMatch();

                $match->forceFill($attributes + [
                    'event_id' => $event->id,
                    'team1' => $team1Id,
                    'team2' => $team2Id,
                ]);

                if ($match->trashed()) {
                    $match->restore();
                }

                $match->save();
                $expectedMatchIds[] = $match->id;
            }

            // Remove fixtures that are no longer part of this demo event.
            EventMatch::query()
                ->where('event_id', $event->id)
                ->whereNotIn('id', $expectedMatchIds)
                ->delete();
        }
    }

    /**
     * Build the persisted attributes for a fixture.
     *
     * @param  array<string, mixed>  $fixture
     * @return array<string, mixed>
     */
    private function fixtureAttributes(array $fixture, int $team1Id, int $team2Id, int $fieldId): array
    {
        $attributes = [
            'field_id' => $fieldId,
            'team1' => $team1Id,
            'team2' => $team2Id,
            'team1_score' => 0,
            'team2_score' => 0,
            'team1_oldScore' => 0,
            'team2_oldScore' => 0,
            'winner' => null,
            'losser' => null,
            'isDraw' => false,
            'submitted' => false,
            'is_abandoned_match' => false,
        ];

        $status = $fixture['status'] ?? 'unplayed';

        if ($status === 'unplayed') {
            return $attributes;
        }

        [$team1Score, $team2Score] = $fixture['score'];

        $attributes['team1_score'] = $team1Score;
        $attributes['team2_score'] = $team2Score;
        $attributes['team1_oldScore'] = $team1Score;
        $attributes['team2_oldScore'] = $team2Score;
        $attributes['submitted'] = true;

        if ($status === 'abandoned') {
            $attributes['is_abandoned_match'] = true;
            $attributes['isDraw'] = true;

            return $attributes;
        }

        if ($status === 'draw' || $team1Score === $team2Score) {
            $attributes['isDraw'] = true;

            return $attributes;
        }

        if ($team1Score > $team2Score) {
            $attributes['winner'] = $team1Id;
            $attributes['losser'] = $team2Id;
        } else {
            $attributes['winner'] = $team2Id;
            $attributes['losser'] = $team1Id;
        }

        return $attributes;
    }
}
