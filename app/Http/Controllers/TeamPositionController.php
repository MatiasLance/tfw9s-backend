<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\TeamPosition\TeamPositionServiceInterface;
use Illuminate\Http\Request;
use App\Models\TeamPosition;

class TeamPositionController extends Controller
{
    protected TeamPositionServiceInterface $teamPositionService;

    public function __construct(TeamPositionServiceInterface $teamPositionService)
    {
        $this->teamPositionService = $teamPositionService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxTeamPositionsPerPage = $request->query('maxTeamPositionsPerPage', null);
        $event = $request->query('event', null);
        $year = $request->query('year', null);
        $agegroup = $request->query('agegroup', null);
        $series = $request->query('series', null);
        $region = $request->query('region', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_teamPosition_per_page' => $maxTeamPositionsPerPage,
            'event' => $event,
            'year' => $year,
            'agegroup' => $agegroup,
            'series' => $series,
            'region' => $region,
        ];

        $teamPositions = $this->teamPositionService->listTeamPositions($filter);

        $message->setContent(200, 'TeamPositions retrieved', '', $teamPositions->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $teamPosition = $this->teamPositionService->retrieveTeamPosition($id);

        $message->setContent(200, 'TeamPosition retrieved', '', [
            'teamPosition' => $teamPosition
        ]);

        return $message->render();
    }

}