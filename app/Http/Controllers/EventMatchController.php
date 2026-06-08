<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\EventMatch\EventMatchServiceInterface;
use Illuminate\Http\Request;
use App\Models\EventMatch;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

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
        $round = $request->query('round', null);
        $maxEventMatchesPerPage = $request->query('maxEventMatchesPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'year' => $year,
            'region' => $region,
            'agegroup' => $agegroup,
            'round' => $round,
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
        $is_abandoned_match = $request->boolean('is_abandoned_match') ?? false;

        $isSuccess = $this->eventMatchService->updateEventMatchScore($id, $team1_score, $team2_score, $is_abandoned_match);

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

    public function revertResultSubmitted(int $id)
    {
        $eventMatch = EventMatch::find($id);

        if(!$eventMatch){
            return response()->json([
                'success' => false,
                'message' => 'Match not found.'
            ]);
        }

        if (!$eventMatch->submitted) {
            return response()->json([
                'success' => true,
                'message' => 'Result is not submitted yet.'
            ]);
        }

        try {
            DB::transaction(function () use ($eventMatch) {
                $eventMatch->update([
                    'submitted' => 0,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Result reverted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to revert match {$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to revert result due to a server error.'
            ], 500);
        }

    }

}



