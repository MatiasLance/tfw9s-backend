<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventMatch;
use App\Models\Team;
use App\Models\TeamPosition;
use Illuminate\Database\Seeder;

class TeamPositionSeeder extends Seeder
{
    /**
     * Build the ladder for every event from its fixtures.
     *
     * Only real teams receive positions. Bye fixtures are stored with
     * team2 = 0 and are intentionally ignored, matching the application's
     * bye rules.
     */
    public function run(): void
    {
        // Clear any legacy bye rows (team_id = 0) before rebuilding the ladder.
        TeamPosition::query()->where('team_id', '<=', 0)->delete();

        Event::query()->orderBy('id')->each(function (Event $event): void {
            $matches = EventMatch::query()
                ->without(['team1', 'team2', 'field', 'event'])
                ->where('event_id', $event->id)
                ->get(['team1', 'team2', 'team1_score', 'team2_score', 'submitted', 'is_abandoned_match']);

            $teamIds = $matches
                ->flatMap(fn (EventMatch $match): array => [(int) $match->team1, (int) $match->team2])
                ->filter(fn (int $teamId): bool => $teamId > 0)
                ->unique()
                ->values();

            if ($teamIds->isEmpty()) {
                TeamPosition::query()->where('event_id', $event->id)->delete();

                return;
            }

            // Remove positions for teams no longer present in the event and
            // make sure no bye position slips through.
            TeamPosition::query()
                ->where('event_id', $event->id)
                ->whereNotIn('team_id', $teamIds->all())
                ->delete();

            $totals = [];

            foreach ($teamIds as $teamId) {
                $totals[$teamId] = [
                    'team_id' => $teamId,
                    'name' => '',
                    'win' => 0,
                    'loss' => 0,
                    'draw' => 0,
                    'for' => 0,
                    'against' => 0,
                    'difference' => 0,
                    'points' => 0,
                ];
            }

            $teamNames = Team::withTrashed()
                ->whereIn('id', $teamIds->all())
                ->pluck('name', 'id');

            foreach ($totals as $teamId => $total) {
                $totals[$teamId]['name'] = (string) ($teamNames[$teamId] ?? '');
            }

            foreach ($matches as $match) {
                $team1Id = (int) $match->team1;
                $team2Id = (int) $match->team2;

                // A bye has team2 = 0, so it is skipped here as well.
                if (! isset($totals[$team1Id], $totals[$team2Id])) {
                    continue;
                }

                if (! $match->submitted) {
                    continue;
                }

                $team1Score = (int) $match->team1_score;
                $team2Score = (int) $match->team2_score;

                $totals[$team1Id]['for'] += $team1Score;
                $totals[$team1Id]['against'] += $team2Score;
                $totals[$team2Id]['for'] += $team2Score;
                $totals[$team2Id]['against'] += $team1Score;

                if ($match->is_abandoned_match || $team1Score === $team2Score) {
                    $totals[$team1Id]['draw']++;
                    $totals[$team2Id]['draw']++;
                    $totals[$team1Id]['points']++;
                    $totals[$team2Id]['points']++;
                } elseif ($team1Score > $team2Score) {
                    $totals[$team1Id]['win']++;
                    $totals[$team1Id]['points'] += 2;
                    $totals[$team2Id]['loss']++;
                } else {
                    $totals[$team2Id]['win']++;
                    $totals[$team2Id]['points'] += 2;
                    $totals[$team1Id]['loss']++;
                }
            }

            $rows = array_values($totals);

            foreach ($rows as $index => $row) {
                $rows[$index]['difference'] = $row['for'] - $row['against'];
            }

            usort($rows, function (array $left, array $right): int {
                return $right['points'] <=> $left['points']
                    ?: $right['difference'] <=> $left['difference']
                    ?: $right['for'] <=> $left['for']
                    ?: strcmp($left['name'], $right['name']);
            });

            foreach ($rows as $index => $row) {
                $position = TeamPosition::query()->firstOrNew([
                    'event_id' => $event->id,
                    'team_id' => $row['team_id'],
                ]);

                $position->forceFill([
                    'position' => $index + 1,
                    'win' => $row['win'],
                    'loss' => $row['loss'],
                    'draw' => $row['draw'],
                    'for' => $row['for'],
                    'against' => $row['against'],
                    'difference' => $row['difference'],
                    'points' => $row['points'],
                ]);

                $position->save();
            }
        });
    }
}
