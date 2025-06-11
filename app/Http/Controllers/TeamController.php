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
        $region = $request->query('region', null);
        $series = $request->query('series', null);
        $seriestype = $request->query('seriestype', null);
        $maxTeamsPerPage = $request->query('maxTeamsPerPage', null);
        $isRegistered = $request->query('isRegistered', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'region' => $region,
            'series' => $series,
            'seriestype' => $seriestype,
            'max_team_per_page' => $maxTeamsPerPage,
            'isRegistered' => $isRegistered,
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
        $agegroup_id = (int)$request->input('agegroup_id');
        $series_id = $request->input('series_id');
        $region_id = $request->input('region_id');
        $type = $request->input('type', 'default');

        $coach_name = $request->input('coach_name');
        $coach_mobile = $request->input('coach_mobile');
        $coach_email = $request->input('coach_email');

        $manager_name = $request->input('manager_name');
        $manager_mobile = $request->input('manager_mobile');
        $manager_email = $request->input('manager_email');

        $player_limit = $request->input('player_limit');

        $coach = [
            'name' => $coach_name,
            'mobile' => $coach_mobile,
            'email' => $coach_email,
        ];

        $manager = [
            'name' => $manager_name,
            'mobile' => $manager_mobile,
            'email' => $manager_email,
        ];

        $media = $request->file('photo') ?? [];

        $team = $this->teamService->createTeam($name, $agegroup_id, $series_id, $coach, $manager, $media, $type, $region_id, $player_limit);

        // $team = $this->teamService->createTeam($name, $field_id, $agegroup_id, $media);

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
        $agegroup_id = $request->input('agegroup_id');
        $series_id = $request->input('series_id');
        $region_id = $request->input('region_id');

        $coach_name = $request->input('coach_name');
        $coach_mobile = $request->input('coach_mobile');
        $coach_email = $request->input('coach_email');

        $manager_name = $request->input('manager_name');
        $manager_mobile = $request->input('manager_mobile');
        $manager_email = $request->input('manager_email');

        $player_limit = $request->input('player_limit');

        $coach = [
            'name' => $coach_name,
            'mobile' => $coach_mobile,
            'email' => $coach_email,
        ];

        $manager = [
            'name' => $manager_name,
            'mobile' => $manager_mobile,
            'email' => $manager_email,
        ];

        $newPhoto = $request->file('photo') ?? [];
        $existingPhoto = $request->input('photo') ?? [];
        $newPhotoCount = count($newPhoto);
        $existingPhotoCount = count($existingPhoto);

        if (
            $request->has('photo') &&
            (
                $newPhotoCount > 0 ||
                $existingPhotoCount > 0
            )
        ) {
            foreach ($existingPhoto as $existingPhotoHash) {
                array_push($newPhoto, $existingPhotoHash);
            }
            $media = $newPhoto;
        } else {
            $media = null;
        }

        $isSuccess = $this->teamService->updateTeam($id, $name, $agegroup_id, $series_id, $coach, $manager, $media, $region_id, $player_limit);

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

    public function all(Request $request, Message $message)
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

        $teams = $this->teamService->allTeams($filter);

        $message->setContent(200, 'Teams retrieved', '', $teams->toArray());

        return $message->render();
    }

        public function trashed(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $seriestype = $request->query('seriestype', null);
        $maxTeamsPerPage = $request->query('maxTeamsPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'seriestype' => $seriestype,
            'max_team_per_page' => $maxTeamsPerPage,
        ];

        $teams = $this->teamService->trashedTeams($filter);

        $message->setContent(200, 'Teams retrieved', '', $teams->toArray());

        return $message->render();
    }

    public function refund(Request $request, Message $message, int $id)
    {
        $amount = $request->input('amount', null);

        $refund = $this->teamService->refundTeam($id, $amount);

        $message->setContent(200, 'Team refunded', '', [
            'refund success:' => $refund
        ]);

        return $message->render();
    }

    public function cancelref(Message $message, int $id)
    {
        $cancel = $this->teamService->cancelrefTeam($id);

        $message->setContent(200, 'Refund canceled', '', [
            'cancel success:' => $cancel
        ]);

        return $message->render();
    }
}