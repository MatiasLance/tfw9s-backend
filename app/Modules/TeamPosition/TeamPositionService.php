<?php

namespace App\Modules\TeamPosition;

use App\Models\TeamPosition;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repository\TeamPositionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TeamPositionService implements TeamPositionServiceInterface
{
    /**
     * TeamPosition Repository
     *
     * @var TeamPositionRepositoryInterface $teamPositionRepository
     */
    protected TeamPositionRepositoryInterface $teamPositionRepository;

    public function __construct(TeamPositionRepositoryInterface $teamPositionRepository)
    {
        $this->teamPositionRepository = $teamPositionRepository;
    }

    public function listTeamPositions(array $filters = []): Paginate
    {
        return $this->teamPositionRepository->listTeamPositions($filters);
    }

    public function listOfTeamPositions(array $filters = [])
    {
        return $this->teamPositionRepository->listOfTeamPositions($filters);
    }

    public function retrieveTeamPosition(int $id): TeamPosition
    {
        return $this->teamPositionRepository->retrieveTeamPosition($id);
    }

    public function createTeamPosition(int $event_id, int $team_id): TeamPosition
    {
        return $this->teamPositionRepository->createTeamPosition($event_id, $team_id);
    }

    public function updateTeamPosition(int $event_id, int $eventMatch_id, array $existingResult): bool
    {
        return $this->teamPositionRepository->updateTeamPosition($event_id, $eventMatch_id, $existingResult);
    }

    public function deleteTeamPosition(TeamPosition $teamPosition): bool
    {
        return $this->teamPositionRepository->deleteTeamPosition($teamPosition->id);
    }
}
