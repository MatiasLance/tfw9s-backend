<?php

namespace App\Modules\EventMatch;

use App\Models\User;
use App\Models\EventMatch;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\EventMatchRepositoryInterface;

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

    public function createEventMatch(int $event_id, string $match_time, int $team1, int $team2): EventMatch
    {
        return $this->eventMatchRepository->createEventMatch($event_id, $match_time, $team1, $team2);
    }

    public function updateEventMatch(int $id, int $event_id, string $match_time, int $team1, int $team2): bool
    {
        return $this->eventMatchRepository->updateEventMatch($id, $event_id, $match_time, $team1, $team2);
    }

    public function updateEventMatchScore(int $id, int $team1_score, int $team2_score): bool
    {
        return $this->eventMatchRepository->updateEventMatchScore($id, $team1_score, $team2_score);
    }

    public function storeResult(int $id, int $team1_score, int $team2_score): bool
    {
        return $this->eventMatchRepository->storeResult($id, $team1_score, $team2_score);
    }

    public function deleteEventMatch(User $initiator, EventMatch $eventMatch): bool
    {
        return $this->eventMatchRepository->deleteEventMatch($eventMatch->id);
    }

}
