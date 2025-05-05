<?php

namespace App\Modules\Team;

use App\Models\User;
use App\Models\Team;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\TeamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TeamService implements TeamServiceInterface
{
    /**
     * Team Repository
     *
     * @var TeamRepositoryInterface $teamRepository
     */
    protected TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function listTeams(array $filters = []): Paginate
    {
        return $this->teamRepository->listTeams($filters);
    }

    public function retrieveTeam(int $id): Team
    {
        return $this->teamRepository->retrieveTeam($id);
    }
    /*
    public function createTeam($name, $description, $field_id, $event_id, $coach, $manager, $media): Team
    {
        return $this->teamRepository->createTeam($name, $description, $field_id, $event_id, $coach, $manager, $media);
    }
    */

    public function createTeam(string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media, string $type): Team
    {
        return $this->teamRepository->createTeam($name, $agegroup_id, $series_id, $coach, $manager, $media, $type);
    }

    public function updateTeam(int $id, string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media): bool
    {
        return $this->teamRepository->updateTeam($id, $name, $agegroup_id, $series_id, $coach, $manager, $media);
    }

    public function deleteTeam(User $initiator, Team $team): bool
    {
        return $this->teamRepository->deleteTeam($team->id);
    }

    public function allTeams(array $filters = []): Paginate
    {
        return $this->teamRepository->allTeams($filters);
    }

    public function trashedTeams(array $filters = []): Paginate
    {
        return $this->teamRepository->trashedTeams($filters);
    }

    public function refundTeam(int $id, int $amount): bool
    {
        return $this->teamRepository->refundTeam($id, $amount);
    }

    public function cancelrefTeam(int $id): bool
    {
        return $this->teamRepository->cancelrefTeam($id);
    }
}
