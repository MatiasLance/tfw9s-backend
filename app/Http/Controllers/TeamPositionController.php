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

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_teamPosition_per_page' => $maxTeamPositionsPerPage,
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

    public function store(Request $request, Message $message)
    {
        $event_id = $request->input('event_id');
        $team_id = $request->input('team_id');

        $teamPosition = $this->teamPositionService->createTeamPosition($event_id, $team_id);

        if ($teamPosition instanceof TeamPosition) {
            $message->setContent(201, 'TeamPosition created', '', [
                'teamPosition' => $teamPosition
            ]);
        } else {
            $message->setContent(400, 'TeamPosition not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message)
    {
        $event_id = $request->input('event_id');
        $eventMatch_id = $request->input('eventMatch_id');

        $isSuccess = $this->teamPositionService->updateTeamPosition($event_id, $eventMatch_id);

        if ($isSuccess) {
            $message->setContent(200, 'TeamPosition updated');
        } else {
            $message->setContent(400, 'TeamPosition not updated');
        }

        return $message->render();
    }

    public function delete(Message $message, int $id)
    {

        $teamPosition = $this->teamPositionService->retrieveTeamPosition($id);

        $isSuccess = $this->teamPositionService->deleteTeamPosition($$teamPosition);

        if ($isSuccess) {
            $message->setContent(200, 'TeamPosition deleted');
        } else {
            $message->setContent(400, 'TeamPosition not updated');
        }

        return $message->render();
    }
}



