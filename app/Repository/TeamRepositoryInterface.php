<?php

namespace App\Repository;

use App\Models\Team;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface TeamRepositoryInterface
{
    /**
     * Maximum teams to be shown per page
     *
     * @var int MAX_PAGE_TEAMS
     */
    public const MAX_PAGE_TEAMS = 12;

    /**
     * Placeholder team name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_team_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of teams.
     *
     * @param array $userFilters
     *
     * @return Paginate<team>
     */
    public function listTeams(array $userFilters = []): Paginate;

    /**
     * Retrieve an team
     *
     * @param int $id
     *
     * @return team
     */
    public function retrieveTeam(int $id): Team;

    /**
     * Create a new team instance
     *
     * @param string $name
     * @param string $description
     * @param int $field_id
     *
     * @return team
     */
    public function createTeam(string $name, string $description, int $field_id): Team;

    /**
     * Update an existing team instance
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
     * Delete an existing team instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteTeam(int $id): bool;

}
