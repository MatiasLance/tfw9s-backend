<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Team\TeamServiceInterface;
use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    protected TeamServiceInterface $teamService;

    public function __construct(TeamServiceInterface $teamService)
    {
        $this->teamService = $teamService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $maxTeamsPerPage = $request->query('maxTeamsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'max_team_per_page' => $maxTeamsPerPage,
        ];

        $teams = $this->teamService->listTeams($filter);

        $message->setContent(200, 'Teams retrieved', '', $teams->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $team = $this->teamService->retrieveTeam($id);

        $message->setContent(200, 'Team retrieved', '', [
            'team' => $team
        ]);

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $field_id = $request->input('field_id');

        $team = $this->teamService->createTeam($name, $description, $field_id);

        if ($team instanceof Team) {
            $message->setContent(201, 'Team created', '', [
                'team' => $team
            ]);
        } else {
            $message->setContent(400, 'Team not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $name = $request->input('name');
        $description = $request->input('description') ?? '';
        $field_id = $request->input('field_id');

        $isSuccess = $this->teamService->updateTeam($id, $name, $description, $field_id);

        if ($isSuccess) {
            $message->setContent(200, 'Team updated');
        } else {
            $message->setContent(400, 'Team not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $team = $this->teamService->retrieveTeam($id);

        $isSuccess = $this->teamService->deleteTeam($user, $team);

        if ($isSuccess) {
            $message->setContent(200, 'Team deleted');
        } else {
            $message->setContent(400, 'Team not updated');
        }

        return $message->render();
    }
}


