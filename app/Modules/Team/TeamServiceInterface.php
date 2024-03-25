<?php

namespace App\Modules\Team;

use App\Models\Team;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface TeamServiceInterface
{
    /**
     * Retrieve a list of teams
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Team>
     */
    public function listTeams(array $filters = []): Paginate;

    /**
     * Retrieve an Team
     *
     * @param int $id
     *
     * @return Team
     */
    public function retrieveTeam(int $id): Team;

    /**
     * Create a new Team
     *
     * @param string $name
     * @param string $description
     * @param int $field_id
     *
     * @return Team
     */
    public function createTeam(string $name, string $description, int $field_id): Team;

    /**
     * Update an existing Team
     *
     * @param int $id
     * @param string $name
     * @param string $description
     * @param int $field_id
     *
     * @return bool
     */
    public function updateTeam(int $id, string $name, string $description, int $field_id): bool;

    /**
     * Delete an existing Team
     *
     * @param User $initiator The user who initiated the delete command
     * @param Team $team The team to be deleted
     *
     * @return bool
     */
    public function deleteTeam(User $initiator, Team $team): bool;

}
