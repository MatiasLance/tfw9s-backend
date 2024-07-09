<?php

namespace App\Modules\TeamLimit;

use App\Models\TeamLimit;
use App\Repository\TeamLimitRepositoryInterface;

class TeamLimitService implements TeamLimitServiceInterface
{
    /**
     * TeamLimit Repository
     *
     * @var TeamLimitRepositoryInterface $teamLimitRepository
     */
    protected TeamLimitRepositoryInterface $teamLimitRepository;

    public function __construct(TeamLimitRepositoryInterface $teamLimitRepository)
    {
        $this->teamLimitRepository = $teamLimitRepository;
    }

    public function listTeamLimits(int $series_id): array
    {
        return $this->teamLimitRepository->listTeamLimits($series_id);
    }

    public function createTeamLimit(int $series_id): bool
    {
        return $this->teamLimitRepository->createTeamLimit($series_id);
    }

    public function updateTeamLimit(array $teamcount): bool
    {
        return $this->teamLimitRepository->updateTeamLimit($teamcount);
    }

    public function deleteTeamLimit(TeamLimit $teamLimit): bool
    {
        return $this->teamLimitRepository->deleteTeamLimit($teamLimit->id);
    }

}

