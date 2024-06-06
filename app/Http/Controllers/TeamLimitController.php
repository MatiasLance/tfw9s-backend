<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\TeamLimit\TeamLimitServiceInterface;
use App\Modules\Http\Message;

class TeamLimitController extends Controller
{
    protected TeamLimitServiceInterface $teamLimitService;

    public function __construct(TeamLimitServiceInterface $teamLimitService)
    {
        $this->teamLimitService = $teamLimitService;
    }

    public function list(Message $message, int $series_id)
    {
        $teamLimits = $this->teamLimitService->listTeamLimits($series_id);

        $message->setContent(200, 'Team Limit retrieved', '', $teamLimits);

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $teamcount = $request->input('teamcount');

        $isSuccess = $this->teamLimitService->updateTeamLimit($id, $teamcount);

        if ($isSuccess) {
            $message->setContent(200, 'Team Limit updated');
        } else {
            $message->setContent(400, 'Team Limit not updated');
        }

        return $message->render();
    }
}
