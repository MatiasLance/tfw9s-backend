<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;
use RuntimeException;

class PlayersSeeder extends Seeder
{
    private const PLAYERS_PER_TEAM = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Team::query()->exists()) {
            throw new RuntimeException('PlayersSeeder requires teams to be seeded first.');
        }

        Team::query()
            ->with('agegroup')
            ->orderBy('id')
            ->each(function (Team $team): void {
                for ($number = 1; $number <= self::PLAYERS_PER_TEAM; $number++) {
                    $email = "team{$team->id}.player{$number}@example.test";
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
            });
    }
}
