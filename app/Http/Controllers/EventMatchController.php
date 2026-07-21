<?php

namespace App\Http\Controllers;

use App\Modules\EventMatch\EventMatchServiceInterface;
use App\Modules\Http\Message;
use Illuminate\Http\Request;

class EventMatchController extends Controller
{
    protected EventMatchServiceInterface $eventMatchService;

    public function __construct(EventMatchServiceInterface $eventMatchService)
    {
        $this->eventMatchService = $eventMatchService;
    }

    public function list(Request $request, Message $message)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'string', 'max:30'],
            'page' => ['nullable', 'integer', 'min:1'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'region' => ['nullable', 'integer', 'min:1'],
            'agegroup' => ['nullable', 'integer', 'min:1'],
            'round' => ['nullable', 'string', 'max:50'],
            'series' => ['nullable', 'string', 'max:255'],
            'series_id' => ['nullable', 'integer', 'min:1'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'status' => ['nullable', 'in:complete,upcoming'],
            'maxEventMatchesPerPage' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $filter = [
            'q' => $validated['q'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'page' => $validated['page'] ?? null,
            'year' => $validated['year'] ?? null,
            'region' => $validated['region'] ?? null,
            'agegroup' => $validated['agegroup'] ?? null,
            'round' => $validated['round'] ?? null,
            'series' => $validated['series'] ?? null,
            'series_id' => $validated['series_id'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
            'status' => $validated['status'] ?? null,
            'max_eventMatch_per_page' => $validated['maxEventMatchesPerPage'] ?? null,
        ];

        $eventMatches = $this->eventMatchService->listEventMatches($filter);

        $message->setContent(200, 'EventMatches retrieved', '', $eventMatches->toArray());

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $eventMatch = $this->eventMatchService->retrieveEventMatch($id);

        $message->setContent(200, 'EventMatch retrieved', '', [
            'eventMatch' => $eventMatch,
        ]);

        return $message->render();
    }

    public function updatescore(Request $request, Message $message, int $id)
    {
        $validated = $request->validate([
            'team1_score' => ['required', 'integer', 'min:0', 'max:999'],
            'team2_score' => ['required', 'integer', 'min:0', 'max:999'],
            'is_abandoned_match' => ['sometimes', 'boolean'],
        ]);

        $isSuccess = $this->eventMatchService->updateEventMatchScore(
            $id,
            (int) $validated['team1_score'],
            (int) $validated['team2_score'],
            $request->boolean('is_abandoned_match')
        );

        if ($isSuccess) {
            $message->setContent(200, 'Event updated');
        } else {
            $message->setContent(400, 'Event not updated');
        }

        return $message->render();
    }

    public function storeResult(Request $request, Message $message, int $id)
    {
        $validated = $request->validate([
            'team1_score' => ['required', 'integer', 'min:0', 'max:999'],
            'team2_score' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $isSuccess = $this->eventMatchService->storeResult(
            $id,
            (int) $validated['team1_score'],
            (int) $validated['team2_score']
        );

        if ($isSuccess) {
            $message->setContent(200, 'Result updated');
        } else {
            $message->setContent(409, 'The result has already been submitted.');
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

    public function revertResultSubmitted(Message $message, int $id)
    {
        $this->eventMatchService->revertResult($id);
        $message->setContent(200, 'Result reverted successfully');

        return $message->render();
    }
}
