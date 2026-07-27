<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListTeamPositionsRequest;
use App\Modules\Http\Message;
use App\Modules\TeamPosition\TeamPositionServiceInterface;

class TeamPositionController extends Controller
{
    protected TeamPositionServiceInterface $teamPositionService;

    public function __construct(TeamPositionServiceInterface $teamPositionService)
    {
        $this->teamPositionService = $teamPositionService;
    }

    public function list(ListTeamPositionsRequest $request, Message $message)
    {
        $teamPositions = $this->teamPositionService->listTeamPositions($request->filters());

        $message->setContent(200, 'TeamPositions retrieved', '', $teamPositions->toArray());

        return $message->render();
    }

    public function listOfTeamPositions(ListTeamPositionsRequest $request, Message $message)
    {
        $teamPositions = $this->teamPositionService->listOfTeamPositions($request->filters());

        $message->setContent(200, 'TeamPositions retrieved', '', $teamPositions);

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $teamPosition = $this->teamPositionService->retrieveTeamPosition($id);

        $message->setContent(200, 'TeamPosition retrieved', '', [
            'teamPosition' => $teamPosition,
        ]);

        return $message->render();
    }
}
