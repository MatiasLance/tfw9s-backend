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
