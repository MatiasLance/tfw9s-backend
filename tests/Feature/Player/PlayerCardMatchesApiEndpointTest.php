<?php

namespace Tests\Feature\Player;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerCardMatchesApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_exact_matches_with_privacy_safe_identifiers(): void
    {
        $team = Team::factory()->create(['name' => 'Gosford Falcons']);
        Player::factory()->forTeam($team)->create([
            'player_firstname' => 'John',
            'player_lastname' => 'Smith',
            'phone_number' => '+61 412 345 678',
            'email' => 'private-parent@example.com',
        ]);

        $response = $this->getJson(
            '/api/v1/players/player-card/matches?'.http_build_query([
                'q' => '  JOHN SMITH  ',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.matches.0.player_name', 'John Smith')
            ->assertJsonPath('data.matches.0.first_name', 'John')
            ->assertJsonPath('data.matches.0.last_name', 'Smith')
            ->assertJsonPath('data.matches.0.masked_phone', '***678')
            ->assertJsonPath('data.matches.0.team_name', 'Gosford Falcons')
            ->assertJsonPath('data.matches.0.match_type', 'exact')
            ->assertJsonMissing(['phone_number' => '+61 412 345 678'])
            ->assertJsonMissing(['email' => 'private-parent@example.com']);

        $this->assertArrayNotHasKey(
            'date_of_birth',
            $response->json('data.matches.0')
        );
    }

    public function test_legacy_name_suggestions_are_also_privacy_safe(): void
    {
        Player::factory()->create([
            'contact_firstname' => 'Private',
            'contact_lastname' => 'Parent',
            'player_firstname' => 'John',
            'player_lastname' => 'Smith',
            'phone_number' => '+61 412 345 678',
            'email' => 'private-parent@example.com',
            'dob' => '2015-03-12',
        ]);

        $response = $this->getJson('/api/v1/players/name/suggest?q=John%20Smith');

        $response
            ->assertOk()
            ->assertJsonPath('data.suggestions.0.player_name', 'John Smith')
            ->assertJsonPath('data.suggestions.0.masked_phone', '***678')
            ->assertJsonMissing(['phone_number' => '+61 412 345 678'])
            ->assertJsonMissing(['email' => 'private-parent@example.com'])
            ->assertJsonMissing(['date_of_birth' => '2015-03-12'])
            ->assertJsonMissing(['parent_first_name' => 'Private'])
            ->assertJsonMissing(['parent_last_name' => 'Parent']);
    }

    public function test_it_finds_minor_spelling_variations_and_partial_names(): void
    {
        $team = Team::factory()->create();
        Player::factory()->forTeam($team)->create([
            'player_firstname' => 'John',
            'player_lastname' => 'Smith',
        ]);

        $this->getJson(
            '/api/v1/players/player-card/matches?'.http_build_query([
                'q' => 'Jon Smith',
            ])
        )
            ->assertOk()
            ->assertJsonPath('data.matches.0.player_name', 'John Smith')
            ->assertJsonPath('data.matches.0.match_type', 'close');

        $this->getJson(
            '/api/v1/players/player-card/matches?'.http_build_query([
                'q' => 'john smi',
            ])
        )
            ->assertOk()
            ->assertJsonPath('data.matches.0.player_name', 'John Smith');
    }

    public function test_it_does_not_return_unrelated_names(): void
    {
        $team = Team::factory()->create();
        Player::factory()->forTeam($team)->create([
            'player_firstname' => 'Jane',
            'player_lastname' => 'Doe',
        ]);

        $this->getJson(
            '/api/v1/players/player-card/matches?'.http_build_query([
                'q' => 'Michael Roberts',
            ])
        )
            ->assertOk()
            ->assertJsonCount(0, 'data.matches');
    }

    public function test_it_excludes_inactive_cards_and_collapses_duplicate_rows(): void
    {
        $team = Team::factory()->create();
        $attributes = [
            'team_id' => $team->id,
            'player_firstname' => 'John',
            'player_lastname' => 'Smith',
            'phone_number' => '+61 412 345 678',
        ];

        Player::factory()->create($attributes);
        Player::factory()->create($attributes);
        $inactivePlayer = Player::factory()->create($attributes);
        $inactivePlayer->delete();

        $this->getJson('/api/v1/players/player-card/matches?q=John%20Smith')
            ->assertOk()
            ->assertJsonCount(1, 'data.matches')
            ->assertJsonPath('data.matches.0.masked_phone', '***678');
    }
}
