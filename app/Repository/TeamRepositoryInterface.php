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
     * @param int $agegroup_id
     * @param int $series_id
     * @param array $coach
     * @param array $manager
     * @param ?array $media
     *
     * @return team
     */
    public function createTeam(string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media): Team;

    /**
     * Update an existing team instance
     *
     * @param int $id
     * @param string $name
     * @param int $agegroup_id
     * @param int $series_id
     * @param array $coach
     * @param array $manager
     * @param ?array $media
     *
     * @return bool
     */
    public function updateTeam(int $id, string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media): bool;

    /**
     * Delete an existing team instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteTeam(int $id): bool;

    /**
     * Retrieve a list of teams.
     *
     * @param array $userFilters
     *
     * @return Paginate<team>
     */
    public function allTeams(array $userFilters = []): Paginate;

}
