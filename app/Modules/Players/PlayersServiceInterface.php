<?php

namespace App\Modules\Players;

use App\Models\Player;
use App\Models\User;
use DateTime;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface PlayersServiceInterface
{
    /**
     * Retrieve a list of players
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Player>
     */
    public function listPlayers(array $filters = []): Paginate;

    /**
     * Create a new Player
     *
     * @param int $id
     * @param string $contact_firstname
     * @param string $contact_lastname
     * @param string $phone_number
     * @param string email
     * @param string player_firstname
     * @param string player_lastname
     * @param string team_name
     * @param DateTime DateTime dob
     * @param string agegroup
     * @param string description
     *
     * @return Player
     */
    public function createPlayers(
        string $contact_firstname,
        string $contact_lastname,
        string $phone_number,
        string $email,
        string $player_firstname,
        string $player_lastname,
        int $team_id,
        DateTime $dob,
        int $agegroup_id,
        ?string $description,
        int $series_id,
        ?array $media
    ): Player;

    /**
     * Update an existing Series
     *
     * @param int $id
     * @param string $contact_firstname
     * @param string $contact_lastname
     * @param string $phone_number
     * @param string email
     * @param string player_firstname
     * @param string player_lastname
     * @param string team_name
     * @param DateTime DateTime dob
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
        int $team_id,
        DateTime $dob,
        int $agegroup_id,
        ?string $description,
        int $series_id,
        ?array $media
    ): bool;

     /**
     * Retrieve an Players
     *
     * @param int $id
     *
     * @return Player
     */
    public function retrievePlayers(int $id): Player;

     /**
     * Delete an existing Players
     *
     * @param User $initiator The user who initiated the delete command
     * @param Player $players The players to be deleted
     *
     * @return bool
     */
    public function deletePlayers(User $initiator, Player $players): bool;
        
    /**
     * Retrieve a list of players
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Player>
     */
    public function trashedPlayers(array $filters = []): Paginate;

    /**
     * Refubnd an Player
     *
     * @param int $id
     * @param int $amount
     *
     * @return Player
     */
    public function refundPlayer(int $id, int $amount): bool;

    /**
     * Cancel a Refund
     *
     * @param int $id
     *
     * @return Player
     */
    public function cancelrefPlayer(int $id): bool;
}
