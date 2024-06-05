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

    public function createTeam($name, $field_id, $agegroup_id, $media): Team
    {
        return $this->teamRepository->createTeam($name, $field_id, $agegroup_id, $media);
    }

    public function updateTeam(int $id, string $name, string $description, $field_id, $event_id, $coach, $manager, $media): bool
    {
        return $this->teamRepository->updateTeam($id, $name, $description, $field_id, $event_id, $coach, $manager, $media);
    }

    public function deleteTeam(User $initiator, Team $team): bool
    {
        return $this->teamRepository->deleteTeam($team->id);
    }

    public function allTeams(array $filters = []): Paginate
    {
        return $this->teamRepository->allTeams($filters);
    }
}
