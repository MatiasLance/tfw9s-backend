<?php

namespace App\Repository;

use App\Models\TeamPosition;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface TeamPositionRepositoryInterface
{
    /**
     * Maximum teamPositions to be shown per page
     *
     * @var int MAX_PAGE_TEAMPOSITIONS
     */
    public const MAX_PAGE_TEAMPOSITIONS = 12;

    /**
     * Placeholder teamPosition name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_teamPosition_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of teamPositions.
     *
     * @param array $userFilters
     *
     * @return Paginate<teamPosition>
     */
    public function listTeamPositions(array $userFilters = []): Paginate;

    /**
     * Retrieve an teamPosition
     *
     * @param int $id
     *
     * @return teamPosition
     */
    public function retrieveTeamPosition(int $id): TeamPosition;

    /**
     * Create a new teamPosition instance
     *
     * @param int $event_id
     * @param int $team_id
     *
     * @return teamPosition
     */
    public function createTeamPosition(int $event_id, int $team_id): TeamPosition;

    /**
     * Update an existing teamPosition instance
     *
     * @param int $event_id
     * @param int $eventMatch_id
     * @param array $existingResult
     *
     * @return bool
     */
    public function updateTeamPosition(int $event_id, int $eventMatch_id, array $existingResult): bool;

    /**
     * Delete an existing teamPosition instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteTeamPosition(int $id): bool;

}
