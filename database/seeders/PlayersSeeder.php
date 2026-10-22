<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;
use RuntimeException;

class PlayersSeeder extends Seeder
{
    /**
     * Number of players created for each demo team.
     */
    private const PLAYERS_PER_TEAM = 16;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teamNames = collect(TeamsSeeder::definitions())
            ->flatMap(fn (array $definition): array => $definition['teams'])
            ->values();

        $teams = Team::query()
            ->with('agegroup')
            ->whereIn('name', $teamNames)
            ->orderBy('id')
            ->get();

        if ($teams->count() !== $teamNames->count()) {
            $missing = $teamNames->diff($teams->pluck('name'))->implode(', ');

            throw new RuntimeException(
                "PlayersSeeder requires the demo teams to be seeded first. Missing: {$missing}."
            );
        }

        foreach ($teams as $team) {
            for ($number = 1; $number <= self::PLAYERS_PER_TEAM; $number++) {
                $email = "demo.team{$team->id}.player{$number}@example.test";

                $attributes = Player::factory()
                    ->forTeam($team)
                    ->make(['email' => $email])
                    ->getAttributes();

                $player = Player::withTrashed()->firstOrNew(['email' => $email]);
                $player->forceFill($attributes);

                if ($player->trashed()) {
                    $player->restore();
                }

                $player->save();
            }
        }
    }
}
