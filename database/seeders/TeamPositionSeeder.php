<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\EventMatch;
use App\Modules\TeamPosition\TeamPositionServiceInterface;

class TeamPositionSeeder extends Seeder
{
        /**
     * TeamPosition Module
     *
     * @var TeamPosition $teamPositionService
     */
    protected TeamPositionServiceInterface $teamPositionService;

    public function __construct(TeamPositionServiceInterface $teamPositionService)
    {
        $this->teamPositionService = $teamPositionService;
    }

    public function run()
    {
        $matches = EventMatch::all();

        foreach ($matches as $match) {
            $event_id = $match->event_id;
            $team1 = $match->team1;
            $team2 = $match->team2;
            $this->teamPositionService->createTeamPosition($event_id, $team1);
            $this->teamPositionService->createTeamPosition($event_id, $team2);
        }
    }
}