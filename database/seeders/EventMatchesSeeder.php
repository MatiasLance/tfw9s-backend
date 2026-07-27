<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Field;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EventMatchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fallbackFieldId = Field::query()->orderBy('id')->value('id');

        if (! $fallbackFieldId) {
            throw new RuntimeException(
                'EventMatchesSeeder requires at least one field.'
            );
        }

        Event::query()->orderBy('id')->get()->each(
            function (Event $event) use ($fallbackFieldId): void {
                $fieldId = Field::query()
                    ->where('region_id', $event->region_id)
                    ->value('id') ?? $fallbackFieldId;
                $selectedTeams = Team::query()
                    ->where('agegroup_id', $event->agegroup_id)
                    ->where('series_id', $event->series_id)
                    ->orderBy('id')
                    ->limit(3)
                    ->pluck('id')
                    ->values();

                if ($selectedTeams->count() < 3) {
                    throw new RuntimeException(
                        "Event {$event->id} requires three teams matching its age group and series."
                    );
                }

                $pairings = [
                    [$selectedTeams[0], $selectedTeams[1]],
                    [$selectedTeams[1], $selectedTeams[2]],
                    [$selectedTeams[2], $selectedTeams[0]],
                ];

                foreach ($pairings as $pairing) {
                    DB::table('event_matches')->updateOrInsert(
                        [
                            'event_id' => $event->id,
                            'team1' => $pairing[0],
                            'team2' => $pairing[1],
                        ],
                        [
                            'field_id' => $fieldId,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        );
    }
}
