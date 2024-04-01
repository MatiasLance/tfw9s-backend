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
        $maxEventMatchesPerPage = $request->query('maxEventMatchesPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
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

    public function result(Request $request, Message $message, int $id)
    {
        $team1_score = $request->input('team1_score');
        $team2_score = $request->input('team2_score');

        $isSuccess = $this->eventMatchService->storeResult($id, $team1_score, $team2_score);

        if ($isSuccess) {
            $message->setContent(200, 'Result updated');
        } else {
            $message->setContent(400, 'Result not updated');
        }

        return $message->render();
    }

}



