<?php

namespace App\Repository;

use App\Models\TeamLimit;

interface TeamLimitRepositoryInterface
{

    /**
     * Retrieve a list of TeamLimits
     *
     * @param int $series_id
     *
     * @return array
     */
    public function listTeamLimits(int $series_id): array;

    /**
     * Create a new TeamLimit instance
     *
     * @param int $series_id
     *
     * @return bool
     */
    public function createTeamLimit(int $series_id): bool;

    /**
     * Update an existing TeamLimit instance
     *
     * @param array $teamcount
     *
     * @return bool
     */
    public function updateTeamLimit(array $teamcount): bool;

    /**
     * Delete an existing TeamLimit instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteTeamLimit(int $id): bool;

}
