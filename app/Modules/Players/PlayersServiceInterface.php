<?php

namespace App\Modules\Players;

use App\Models\Players;
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
     * @return Paginate<Players>
     */
    public function listPlayers(array $filters = []): Paginate;

    /**
     * Create a new Players
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
     * @return Players
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
    ): Players;

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
        string $team_name,
        DateTime $dob,
        string $agegroup,
        string $description
    ): bool;

     /**
     * Retrieve an Players
     *
     * @param int $id
     *
     * @return Players
     */
    public function retrievePlayers(int $id): Players;

     /**
     * Delete an existing Players
     *
     * @param User $initiator The user who initiated the delete command
     * @param Players $players The players to be deleted
     *
     * @return bool
     */
    public function deletePlayers(User $initiator, Players $players): bool;
}
