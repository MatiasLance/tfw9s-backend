<?php

namespace App\Modules\EventMatch;

use App\Models\User;
use App\Models\EventMatch;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\EventMatchRepositoryInterface;
use Doctrine\Common\EventManager;
use Illuminate\Database\Eloquent\Collection;

class EventMatchService implements EventMatchServiceInterface
{
    /**
     * EventMatch Repository
     *
     * @var EventMatchRepositoryInterface $eventMatchRepository
     */
    protected EventMatchRepositoryInterface $eventMatchRepository;

    public function __construct(EventMatchRepositoryInterface $eventMatchRepository)
    {
        $this->eventMatchRepository = $eventMatchRepository;
    }

    public function listEventMatches(array $filters = []): Paginate
    {
        return $this->eventMatchRepository->listEventMatches($filters);
    }

    public function retrieveEventMatch(int $id): EventMatch
    {
        return $this->eventMatchRepository->retrieveEventMatch($id);
    }

    public function createEventMatch(int $event_id, string $match_time, int $team1, int $team2, int $team1_score, int $team2_score): EventMatch
    {
        list($winner, $losser, $isDraw) = $this->decision($team1, $team1_score, $team2, $team2_score);

        return $this->eventMatchRepository->createEventMatch($event_id, $match_time, $team1, $team2, $team1_score, $team2_score, $winner, $losser, $isDraw);
    }

    public function updateEventMatch(int $id, int $event_id, string $match_time, int $team1, int $team2, int $team1_score, int $team2_score): bool
    {
        list($winner, $losser, $isDraw) = $this->decision($team1, $team1_score, $team2, $team2_score);

        return $this->eventMatchRepository->updateEventMatch($id, $event_id, $match_time, $team1, $team2, $team1_score, $team2_score, $winner, $losser, $isDraw);
    }

    public function deleteEventMatch(User $initiator, EventMatch $eventMatch): bool
    {
        return $this->eventMatchRepository->deleteEventMatch($eventMatch->id);
    }

    private function decision(int $team1, int $team1_score, int $team2, int $team2_score): array
    {
        $winner = null;
        $losser = null;
        $isDraw = false;

        if ($team1_score > $team2_score) {
            $winner = $team1;
            $losser = $team2;
        } elseif ($team1_score < $team2_score) {
            $winner = $team2;
            $losser = $team1;
        } else {
            $isDraw = true;
        }

        return [$winner, $losser, $isDraw];

    }
}
