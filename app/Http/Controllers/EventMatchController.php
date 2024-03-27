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

    public function store(Request $request, Message $message)
    {
        $event_id = $request->input('event_id');
        $match_time = $request->input('match_time');
        $team1 = $request->input('team1');
        $team2 = $request->input('team2');
        $team1_score = $request->input('team1_score');
        $team2_score = $request->input('team2_score');

        $eventMatch = $this->eventMatchService->createEventMatch($event_id, $match_time, $team1, $team2, $team1_score, $team2_score);

        if ($eventMatch instanceof EventMatch) {
            $message->setContent(201, 'EventMatch created', '', [
                'eventMatch' => $eventMatch
            ]);
        } else {
            $message->setContent(400, 'EventMatch not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $event_id = $request->input('event_id');
        $match_time = $request->input('match_time');
        $team1 = $request->input('team1');
        $team2 = $request->input('team2');
        $team1_score = $request->input('team1_score');
        $team2_score = $request->input('team2_score');

        $isSuccess = $this->eventMatchService->updateEventMatch($id, $event_id, $match_time, $team1, $team2, $team1_score, $team2_score);

        if ($isSuccess) {
            $message->setContent(200, 'EventMatch updated');
        } else {
            $message->setContent(400, 'EventMatch not updated');
        }

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {
        $user = $request->user();
        $eventMatch = $this->eventMatchService->retrieveEventMatch($id);

        $isSuccess = $this->eventMatchService->deleteEventMatch($user, $eventMatch);

        if ($isSuccess) {
            $message->setContent(200, 'EventMatch deleted');
        } else {
            $message->setContent(400, 'EventMatch not updated');
        }

        return $message->render();
    }
}



