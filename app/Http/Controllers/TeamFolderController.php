<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Http\Message;
use App\Modules\TeamFolder\TeamFolderServiceInterface;

class TeamFolderController extends Controller
{
    protected TeamFolderServiceInterface $teamFolderService;

    public function __construct(TeamFolderServiceInterface $teamFolderService)
    {
        $this->teamFolderService = $teamFolderService;
    }

    public function retrieve(Message $message, int $id)
    {
        $teamFolder = $this->teamFolderService->retrieveTeamFolder($id);

        $message->setContent(200, 'TeamFolder retrieved', '', [
            'teamFolder' => $teamFolder
        ]);

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $title = $request->input('title');
        $content = $request->input('content');

        $isSuccess = $this->teamFolderService->updateTeamFolder($id, $title, $content);

        if ($isSuccess) {
            $message->setContent(200, 'Team updated');
        } else {
            $message->setContent(400, 'Team not updated');
        }

        return $message->render();
    }

}
