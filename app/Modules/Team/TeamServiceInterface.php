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
     * @param int $agegroup_id
     * @param int $series_id
     * @param array $coach
     * @param array $manager
     * @param ?array $media
     * @param int $region_id
     * @param int $player_limit
     * @param int $discount_id
     *
     * @return Team
     */

    /*public function createTeam(string $name, string $description, int $field_id, int $event_id, array $coach, array $manager, ?array $media): Team; */
    public function createTeam(string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media, string $type, int $region_id, int $player_limit, int $discount_id): Team;

    /**
     * Update an existing Team
     *
     * @param int $id
     * @param string $name
     * @param int $agegroup_id
     * @param int $series_id
     * @param array $coach
     * @param array $manager
     * @param ?array $media
     * @param int $region_id
     * @param int $player_limit
     * @param int $discount_id
     *
     * @return bool
     */
    public function updateTeam(int $id, string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media, int $region_id, int $player_limit, int $discount_id): bool;

    /**
     * Delete an existing Team
     *
     * @param User $initiator The user who initiated the delete command
     * @param Team $team The team to be deleted
     *
     * @return bool
     */
    public function deleteTeam(User $initiator, Team $team): bool;

    /**
     * Retrieve a list of teams
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Team>
     */
    public function allTeams(array $filters = []): Paginate;

    /**
     * Retrieve a list of teams
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Team>
     */
    public function trashedTeams(array $filters = []): Paginate;

    /**
     * Refubnd an Team
     *
     * @param int $id
     * @param int $amount
     *
     * @return Team
     */
    public function refundTeam(int $id, int $amount): bool;

    /**
     * Cancel a Refund
     *
     * @param int $id
     *
     * @return Team
     */
    public function cancelrefTeam(int $id): bool;

    /**
     * Generate Team or Individual Registration Link
     *
     * @param int $id
     *
     * @return string
     */
    public function generateTeamAndIndividualRegistrationUrl(int $id): string;

    /**
     * Generate Player Registration Link
     *
     * @param int $id
     *
     * @return string
     */
    public function generatePlayerRegistrationUrl(int $id): string;

}