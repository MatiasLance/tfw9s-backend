<?php

namespace App\Repository;

use App\Models\Player;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use DateTime;

interface PlayersRepositoryInterface
{
    /**
     * Maximum players to be shown per page
     *
     * @var int MAX_PAGE_TEAMS
     */
    public const MAX_PAGE_TEAMS = 12;

    /**
     * Placeholder players name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_players_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of players.
     *
     * @param array $playersFilters
     *
     * @return Paginate<players>
     */
    public function listPlayers(array $playersFilters = []): Paginate;

    /**
     * Create a new Event
     *
     * @param int $id
     * @param string $contact_firstname
     * @param string $contact_lastname
     * @param string $phone_number
     * @param string email
     * @param string player_firstname
     * @param string player_lastname
     * @param string team_name
     * @param int DateTime dob
     * @param string agegroup
     * @param string description
     *
     * @return players
     */
    public function createPlayers(
        string $contact_firstname,
        string $contact_lastname,
        string $phone_number,
        string $email,
        string $player_firstname,
        string $player_lastname,
        string $team_name,
        DateTime $dob,
        string $agegroup,
        string $description,
    ): Player;

    /**
     * Update an existing series instance
     *
     * @param int $id
     * @param string $contact_firstname
     * @param string $contact_lastname
     * @param string $phone_number
     * @param string email
     * @param string player_firstname
     * @param string player_lastname
     * @param string team_name
     * @param int DateTime dob
     * @param string agegroup
     * @param string description
     *
     * @return bool
     */
    public function updatePlayers(
        int $id,
        string $contact_firstname,
        string $contact_lastname,
        string $phone_number,
        string $email,
        string $player_firstname,
        string $player_lastname,
        string $team_name,
        DateTime $dob,
        string $agegroup,
        string $description
    ): bool;

     /**
     * Retrieve an players
     *
     * @param int $id
     *
     * @return players
     */
    public function retrievePlayers(int $id): Player;

    /**
     * Delete an existing players instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deletePlayers(int $id): bool;

        /**
     * Retrieve a list of players.
     *
     * @param array $playersFilters
     *
     * @return Paginate<players>
     */
    public function trashedPlayers(array $playersFilters = []): Paginate;


        /**
     * Refubnd an Player
     *
     * @param int $id
     *
     * @return Player
     */
    public function refundPlayer(int $id): bool;

    /**
     * Cancel a Refund
     *
     * @param int $id
     *
     * @return Player
     */
    public function cancelrefPlayer(int $id): bool;
}
