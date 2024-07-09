<?php

namespace App\Modules\TeamLimit;

use App\Models\TeamLimit;

interface TeamLimitServiceInterface
{

    /**
     * Retrieve a list of teamLimits
     *
     * @param int $series_id
     *
     * @return array
     */
    public function listTeamLimits(int $series_id): array;

    /**
     * Create a new TeamLimit
     *
     * @param int $series_id
     *
     * @return bool
     */
    public function createTeamLimit(int $series_id): bool;

    /**
     * Update an existing TeamLimit
     *
     * @param array teamlimit
     *
     * @return bool
     */
    public function updateTeamLimit(array $teamcount): bool;

    /**
     * Delete an existing TeamLimit
     *
     * @param TeamLimit $teamLimit The teamLimit to be deleted
     *
     * @return bool
     */
    public function deleteTeamLimit(TeamLimit $teamLimit): bool;

}

