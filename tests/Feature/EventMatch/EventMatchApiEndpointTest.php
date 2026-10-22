<?php

namespace Tests\Feature\EventMatch;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventMatchApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    private int $eventId;

    private int $matchId;

    private int $team1Id;

    private int $team2Id;

    protected function setUp(): void
    {
        parent::setUp();

        $now = now();
        $regionId = DB::table('regions')->insertGetId([
            'name' => 'Central Coast',
            'description' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $ageGroupId = DB::table('age_groups')->insertGetId([
            'name' => 'Open',
            'min_age' => 18,
            'max_age' => 99,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $seriesId = DB::table('series')->insertGetId([
            'name' => 'Test Series',
            'type' => 'tournament',
            'description' => '',
            'price' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $fieldId = DB::table('fields')->insertGetId([
            'name' => 'Field 1',
            'description' => '',
            'region_id' => $regionId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->team1Id = $this->createTeam('Tuggerah Division 1', $ageGroupId, $seriesId, $regionId, $now);
        $this->team2Id = $this->createTeam('Tuggerah Division 2', $ageGroupId, $seriesId, $regionId, $now);
        $this->eventId = DB::table('events')->insertGetId([
            'time' => '19:15',
            'round' => 'round',
            'event_date' => '2026-07-21',
            'region_id' => $regionId,
            'agegroup_id' => $ageGroupId,
            'series_id' => $seriesId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->matchId = DB::table('event_matches')->insertGetId([
            'event_id' => $this->eventId,
            'field_id' => $fieldId,
            'team1' => $this->team1Id,
            'team2' => $this->team2Id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([$this->team1Id, $this->team2Id] as $position => $teamId) {
            DB::table('team_positions')->insert([
                'event_id' => $this->eventId,
                'position' => $position,
                'team_id' => $teamId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function test_result_mutations_require_an_admin(): void
    {
        $payload = ['team1_score' => 3, 'team2_score' => 1];

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", $payload)
            ->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", $payload)
            ->assertForbidden();
    }

    public function test_submit_edit_and_revert_rebuild_the_standings(): void
    {
        $this->actingAsAdmin();

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", [
            'team1_score' => 3,
            'team2_score' => 1,
        ])->assertOk();

        $this->assertPosition($this->team1Id, 1, 0, 3, 1, 2, 2);
        $this->assertPosition($this->team2Id, 0, 1, 1, 3, -2, 0);

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", [
            'team1_score' => 3,
            'team2_score' => 1,
        ])->assertStatus(409);
        $this->assertPosition($this->team1Id, 1, 0, 3, 1, 2, 2);

        $this->postJson("/api/v1/eventmatches/update/{$this->matchId}", [
            'team1_score' => 2,
            'team2_score' => 4,
            'is_abandoned_match' => false,
        ])->assertOk();
        $this->assertPosition($this->team1Id, 0, 1, 2, 4, -2, 0);
        $this->assertPosition($this->team2Id, 1, 0, 4, 2, 2, 2);

        $this->postJson("/api/v1/eventmatches/revert/{$this->matchId}")
            ->assertOk();
        $this->assertPosition($this->team1Id, 0, 0, 0, 0, 0, 0);
        $this->assertPosition($this->team2Id, 0, 0, 0, 0, 0, 0);
    }

    public function test_public_filters_are_applied_before_pagination(): void
    {
        $this->getJson('/api/v1/eventmatches?'.http_build_query([
            'q' => 'Division 1',
            'status' => 'upcoming',
            'year' => 2026,
            'page' => 1,
            'maxEventMatchesPerPage' => 10,
        ]))
            ->assertOk()
            ->assertJsonPath('data.total_items', 1)
            ->assertJsonCount(1, 'data.eventMatches')
            ->assertJsonPath('data.eventMatches.0.team1.name', 'Tuggerah Division 1')
            ->assertJsonPath('data.eventMatches.0.event.series.name', 'Test Series')
            ->assertJsonPath('data.eventMatches.0.field.name', 'Field 1');

        $this->getJson('/api/v1/eventmatches?status=complete')
            ->assertOk()
            ->assertJsonPath('data.total_items', 0);
    }

    public function test_scores_are_validated(): void
    {
        $this->actingAsAdmin();

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", [
            'team1_score' => -1,
            'team2_score' => 'invalid',
        ])->assertUnprocessable();
    }

    public function test_score_update_normalizes_legacy_multipart_boolean_strings(): void
    {
        $this->actingAsAdmin();

        $this->post("/api/v1/eventmatches/update/{$this->matchId}", [
            'team1_score' => '2',
            'team2_score' => '2',
            'is_abandoned_match' => 'true',
        ])->assertOk();

        $this->assertDatabaseHas('event_matches', [
            'id' => $this->matchId,
            'team1_score' => 2,
            'team2_score' => 2,
            'is_abandoned_match' => 1,
            'isDraw' => 1,
        ]);

        $this->post("/api/v1/eventmatches/update/{$this->matchId}", [
            'team1_score' => '3',
            'team2_score' => '1',
            'is_abandoned_match' => 'false',
        ])->assertOk();

        $this->assertDatabaseHas('event_matches', [
            'id' => $this->matchId,
            'is_abandoned_match' => 0,
            'isDraw' => 0,
        ]);
    }

    public function test_invalid_abandoned_match_values_are_rejected(): void
    {
        $this->actingAsAdmin();

        $this->postJson("/api/v1/eventmatches/update/{$this->matchId}", [
            'team1_score' => 2,
            'team2_score' => 2,
            'is_abandoned_match' => 'not-a-boolean',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('is_abandoned_match');
    }

    public function test_an_abandoned_result_can_be_submitted_directly(): void
    {
        $this->actingAsAdmin();

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", [
            'team1_score' => 0,
            'team2_score' => 0,
            'is_abandoned_match' => true,
        ])->assertOk();

        $this->assertDatabaseHas('event_matches', [
            'id' => $this->matchId,
            'submitted' => 1,
            'is_abandoned_match' => 1,
            'isDraw' => 1,
            'winner' => null,
            'losser' => null,
        ]);
        $this->assertPosition($this->team1Id, 0, 0, 0, 0, 0, 1);
        $this->assertPosition($this->team2Id, 0, 0, 0, 0, 0, 1);
    }

    public function test_public_ladder_filters_use_the_event_competition_metadata(): void
    {
        $this->actingAsAdmin();

        $event = DB::table('events')->find($this->eventId);
        DB::table('age_groups')->where('id', $event->agegroup_id)->update(['name' => '6']);
        DB::table('series')->where('id', $event->series_id)->update([
            'name' => 'Weekly Series',
            'type' => 'weekly',
        ]);

        $otherAgeGroupId = DB::table('age_groups')->insertGetId([
            'name' => '7',
            'min_age' => 6,
            'max_age' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherSeriesId = DB::table('series')->insertGetId([
            'name' => 'Different Series',
            'type' => 'tournament',
            'description' => '',
            'price' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('teams')
            ->whereIn('id', [$this->team1Id, $this->team2Id])
            ->update([
                'agegroup_id' => $otherAgeGroupId,
                'series_id' => $otherSeriesId,
            ]);

        $this->postJson("/api/v1/eventmatches/{$this->matchId}", [
            'team1_score' => 3,
            'team2_score' => 1,
        ])->assertOk();

        $filters = [
            'year' => 2026,
            'agegroup' => $event->agegroup_id,
            'series' => $event->series_id,
            'round' => 'round',
            'sort' => 'points',
        ];

        $this->getJson('/api/v1/teampositions/list?'.http_build_query($filters))
            ->assertOk()
            ->assertJsonCount(2, 'data.all_positions')
            ->assertJsonPath('data.total_items', 2)
            ->assertJsonPath('data.all_positions.0.team.name', 'Tuggerah Division 1')
            ->assertJsonPath('data.all_positions.0.points', 2);

        $this->getJson('/api/v1/teampositions?'.http_build_query($filters))
            ->assertOk()
            ->assertJsonPath('data.total_items', 2);

        foreach ([
            ['year' => 2025],
            ['agegroup' => $otherAgeGroupId],
            ['series' => $otherSeriesId],
            ['round' => 'final'],
        ] as $differentFilter) {
            $this->getJson('/api/v1/teampositions/list?'.http_build_query(
                array_merge($filters, $differentFilter)
            ))
                ->assertOk()
                ->assertJsonCount(0, 'data.all_positions');
        }
    }

    public function test_event_schedule_mutations_are_protected_and_validate_collisions(): void
    {
        $payload = [
            'time' => '19:15',
            'round' => 'round',
            'region_id' => DB::table('regions')->value('id'),
            'agegroup_id' => DB::table('age_groups')->value('id'),
            'series_id' => DB::table('series')->value('id'),
            'datetime' => '2026-07-21',
            'matches' => [[
                'field_id' => DB::table('fields')->value('id'),
                'team1' => $this->team1Id,
                'team2' => $this->team1Id,
            ]],
        ];

        $this->postJson('/api/v1/events', $payload)->assertUnauthorized();

        $this->actingAsAdmin();
        $this->postJson('/api/v1/events', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('matches.0.team2');
    }

    public function test_grouped_fixture_time_can_be_updated_with_shared_fields_and_teams(): void
    {
        $this->actingAsAdmin();

        $event = DB::table('events')->find($this->eventId);
        $fieldId = DB::table('event_matches')->where('id', $this->matchId)->value('field_id');
        $team3Id = $this->createTeam(
            'Tuggerah Division 3',
            $event->agegroup_id,
            $event->series_id,
            $event->region_id,
            now()
        );
        $match2Id = DB::table('event_matches')->insertGetId([
            'event_id' => $this->eventId,
            'field_id' => $fieldId,
            'team1' => $this->team2Id,
            'team2' => $team3Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $match3Id = DB::table('event_matches')->insertGetId([
            'event_id' => $this->eventId,
            'field_id' => $fieldId,
            'team1' => $team3Id,
            'team2' => $this->team1Id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'time' => '08:00',
            'round' => $event->round,
            'region_id' => $event->region_id,
            'agegroup_id' => $event->agegroup_id,
            'datetime' => $event->event_date,
            'matches' => [
                [
                    'id' => $this->matchId,
                    'field_id' => $fieldId,
                    'team1' => $this->team1Id,
                    'team2' => $this->team2Id,
                ],
                [
                    'id' => $match2Id,
                    'field_id' => $fieldId,
                    'team1' => $this->team2Id,
                    'team2' => $team3Id,
                ],
                [
                    'id' => $match3Id,
                    'field_id' => $fieldId,
                    'team1' => $team3Id,
                    'team2' => $this->team1Id,
                ],
            ],
        ];

        $this->postJson("/api/v1/events/{$this->eventId}", $payload)
            ->assertOk();

        $this->assertDatabaseHas('events', [
            'id' => $this->eventId,
            'time' => '08:00',
        ]);
        $this->getJson("/api/v1/events/{$this->eventId}")
            ->assertOk()
            ->assertJsonPath('data.event.time', '08:00');

        $payload['time'] = '8:00';
        $this->postJson("/api/v1/events/{$this->eventId}", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('time');
    }

    public function test_event_api_normalizes_stored_time_seconds_to_hours_and_minutes(): void
    {
        DB::table('events')->where('id', $this->eventId)->update([
            'time' => '19:15:30',
        ]);

        $this->getJson("/api/v1/events/{$this->eventId}")
            ->assertOk()
            ->assertJsonPath('data.event.time', '19:15');
    }

    public function test_bye_fixtures_do_not_create_unknown_team_positions(): void
    {
        $this->actingAsAdmin();

        $eventId = $this->createEventWithByeFixture();

        $this->assertDatabaseHas('event_matches', [
            'event_id' => $eventId,
            'team1' => $this->team1Id,
            'team2' => 0,
        ]);

        $this->assertDatabaseMissing('team_positions', [
            'event_id' => $eventId,
            'team_id' => 0,
        ]);

        $this->getJson('/api/v1/teampositions/list?'.http_build_query(['event' => $eventId]))
            ->assertOk()
            ->assertJsonCount(1, 'data.all_positions')
            ->assertJsonPath('data.all_positions.0.team_id', $this->team1Id)
            ->assertJsonPath('data.all_positions.0.team.name', 'Tuggerah Division 1');
    }

    public function test_editing_a_fixture_to_a_bye_prunes_the_stale_position(): void
    {
        $this->actingAsAdmin();

        $this->postJson("/api/v1/events/{$this->eventId}", $this->fixtureEventPayload([
            ['id' => $this->matchId, 'field_id' => $this->fieldId(), 'team1' => $this->team1Id, 'team2' => 0],
        ]))->assertOk();

        $this->assertDatabaseMissing('team_positions', [
            'event_id' => $this->eventId,
            'team_id' => $this->team2Id,
        ]);
        $this->assertDatabaseMissing('team_positions', [
            'event_id' => $this->eventId,
            'team_id' => 0,
        ]);
        $this->assertDatabaseHas('team_positions', [
            'event_id' => $this->eventId,
            'team_id' => $this->team1Id,
        ]);

        $this->getJson('/api/v1/teampositions/list?'.http_build_query(['event' => $this->eventId]))
            ->assertOk()
            ->assertJsonCount(1, 'data.all_positions');
    }

    public function test_editing_a_bye_back_to_a_team_restores_its_position(): void
    {
        $this->actingAsAdmin();

        $eventId = $this->createEventWithByeFixture();
        $matchId = DB::table('event_matches')->where('event_id', $eventId)->value('id');

        $this->postJson("/api/v1/events/{$eventId}", $this->fixtureEventPayload([
            ['id' => $matchId, 'field_id' => $this->fieldId(), 'team1' => $this->team1Id, 'team2' => $this->team2Id],
        ]))->assertOk();

        $this->assertDatabaseHas('team_positions', [
            'event_id' => $eventId,
            'team_id' => $this->team2Id,
        ]);
        $this->assertDatabaseMissing('team_positions', [
            'event_id' => $eventId,
            'team_id' => 0,
        ]);
    }

    public function test_teams_that_still_play_other_matches_keep_their_position(): void
    {
        $this->actingAsAdmin();

        $team3Id = $this->createTeam('Ourimbah Ravens', DB::table('age_groups')->value('id'), DB::table('series')->value('id'), DB::table('regions')->value('id'), now());

        $matches = [
            ['field_id' => $this->fieldId(), 'team1' => $this->team1Id, 'team2' => $this->team2Id],
            ['field_id' => $this->fieldId(), 'team1' => $this->team2Id, 'team2' => $team3Id],
        ];

        $created = $this->postJson('/api/v1/events', $this->fixtureEventPayload($matches))->assertCreated();
        $eventId = $created->json('data.event.id');
        $matchIds = DB::table('event_matches')->where('event_id', $eventId)->orderBy('id')->pluck('id');

        $this->assertCount(2, $matchIds);

        // Turn the first match into a bye. Team 2 must keep its ladder entry
        // because it still appears in the second match.
        $this->postJson("/api/v1/events/{$eventId}", $this->fixtureEventPayload([
            ['id' => $matchIds[0], 'field_id' => $this->fieldId(), 'team1' => $this->team1Id, 'team2' => 0],
            ['id' => $matchIds[1], 'field_id' => $this->fieldId(), 'team1' => $this->team2Id, 'team2' => $team3Id],
        ]))->assertOk();

        foreach ([$this->team1Id, $this->team2Id, $team3Id] as $teamId) {
            $this->assertDatabaseHas('team_positions', [
                'event_id' => $eventId,
                'team_id' => $teamId,
            ]);
        }

        $this->assertDatabaseMissing('team_positions', [
            'event_id' => $eventId,
            'team_id' => 0,
        ]);
    }

    public function test_the_ladder_excludes_legacy_bye_positions(): void
    {
        $now = now();
        DB::table('team_positions')->insert([
            'event_id' => $this->eventId,
            'position' => 3,
            'team_id' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->getJson('/api/v1/teampositions/list?'.http_build_query(['event' => $this->eventId]))
            ->assertOk()
            ->assertJsonCount(2, 'data.all_positions');

        $teamIds = collect($response->json('data.all_positions'))->pluck('team_id');

        $this->assertEqualsCanonicalizing([$this->team1Id, $this->team2Id], $teamIds->all());
    }

    private function fixtureEventPayload(array $matches): array
    {
        return [
            'time' => '19:15',
            'round' => 'round',
            'region_id' => DB::table('regions')->value('id'),
            'agegroup_id' => DB::table('age_groups')->value('id'),
            'series_id' => DB::table('series')->value('id'),
            'datetime' => '2026-10-22',
            'matches' => $matches,
        ];
    }

    private function createEventWithByeFixture(): int
    {
        $response = $this->postJson('/api/v1/events', $this->fixtureEventPayload([
            ['field_id' => $this->fieldId(), 'team1' => $this->team1Id, 'team2' => 0],
        ]))->assertCreated();

        return $response->json('data.event.id');
    }

    private function fieldId(): int
    {
        return (int) DB::table('fields')->value('id');
    }

    private function actingAsAdmin(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
    }

    private function createTeam(string $name, int $ageGroupId, int $seriesId, int $regionId, $now): int
    {
        return DB::table('teams')->insertGetId([
            'name' => $name,
            'agegroup_id' => $ageGroupId,
            'series_id' => $seriesId,
            'region_id' => $regionId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function assertPosition(
        int $teamId,
        int $wins,
        int $losses,
        int $for,
        int $against,
        int $difference,
        int $points
    ): void {
        $this->assertDatabaseHas('team_positions', [
            'event_id' => $this->eventId,
            'team_id' => $teamId,
            'win' => $wins,
            'loss' => $losses,
            'for' => $for,
            'against' => $against,
            'difference' => $difference,
            'points' => $points,
        ]);
    }
}
