<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\EventMatch\EventMatchServiceInterface;
use Illuminate\Http\Request;
use App\Models\EventMatch;

class EventMatchController extends Controller
{
    protected EventMatchServiceInterface $eventMatchService;

    public function __construct(EventMatchServiceInterface $eventMatchService)
    {
        $this->eventMatchService = $eventMatchService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $year = $request->query('year', null);
        $region = $request->query('region', null);
        $agegroup = $request->query('agegroup', null);
        $maxEventMatchesPerPage = $request->query('maxEventMatchesPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'year' => $year,
            'region' => $region,
            'agegroup' => $agegroup,
            'max_eventMatch_per_page' => $maxEventMatchesPerPage,
        ];

        $eventMatches = $this->eventMatchService->listEventMatches($filter);

        $message->setContent(200, 'EventMatches retrieved', '', $eventMatches->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $eventMatch = $this->eventMatchService->retrieveEventMatch($id);

        $message->setContent(200, 'EventMatch retrieved', '', [
            'eventMatch' => $eventMatch
        ]);

        return $message->render();
    }

    public function updatescore(Request $request, Message $message, int $id)
    {
        $team1_score = $request->input('team1_score') ?? '';
        $team2_score = $request->input('team2_score') ?? '';

        $isSuccess = $this->eventMatchService->updateEventMatchScore($id, $team1_score, $team2_score);

        if ($isSuccess) {
            $message->setContent(200, 'Event updated');
        } else {
            $message->setContent(400, 'Event not updated');
        }

        return $message->render();
    }

    public function storeResult(Request $request, Message $message, int $id)
    {
        $team1_score = $request->input('team1_score');
        $team2_score = $request->input('team2_score');

        $isSuccess = $this->eventMatchService->storeResult($id, $team1_score, $team2_score);

        if ($isSuccess) {
            $message->setContent(200, 'Result updated');
        } else {
            $message->setContent(400, 'The result has already been submitted.');
        }

        return $message->render();
    }

    public function addVideo(Request $request, Message $message, int $id)
    {
        $video = $request->file('video');

        $isSuccess = $this->eventMatchService->addVideo($id, $video);

        if ($isSuccess) {
            $message->setContent(200, 'Upload Successful');
        } else {
            $message->setContent(400, 'Upload Unsuccessful ');
        }

        return $message->render();
    }

}



