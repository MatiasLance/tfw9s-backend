<?php

namespace App\Modules\TeamPosition;

use App\Models\TeamPosition;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface teamPositionServiceInterface
{
    /**
     * Retrieve a list of teamPositions
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<teamPosition>
     */
    public function listTeamPositions(array $filters = []): Paginate;

    public function listOfTeamPositions(array $filters = []);

    /**
     * Retrieve an teamPosition
     *
     * @param int $id
     *
     * @return teamPosition
     */
    public function retrieveTeamPosition(int $id): TeamPosition;

    /**
     * Create a new teamPosition
     *
     * @param int $event_id
     * @param int $team_id
     *
     * @return teamPosition
     */
    public function createTeamPosition(int $event_id, int $team_id): TeamPosition;

    /**
     * Update an existing teamPosition
     *
     * @param int $event_id
     * @param int $eventMatch_id
     * @param array $existingResult
     *
     * @return bool
     */
    public function updateTeamPosition(int $event_id, int $eventMatch_id, array $existingResult): bool;

    /**
     * Delete an existing teamPosition
     *
     * @param teamPosition $teamPosition The teamPosition to be deleted
     *
     * @return bool
     */
    public function deleteTeamPosition(TeamPosition $teamPosition): bool;

}
