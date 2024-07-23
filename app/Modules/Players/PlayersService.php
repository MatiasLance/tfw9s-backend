<?php

namespace App\Modules\Players;

use App\Models\User;
use App\Models\Player;
use DateTime;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\PlayersRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PlayersService implements PlayersServiceInterface
{
    /**
     * Players Repository
     *
     * @var PlayersRepositoryInterface $playersRepository
     */
    protected PlayersRepositoryInterface $playersRepository;

    public function __construct(PlayersRepositoryInterface $playersRepository)
    {
        $this->playersRepository = $playersRepository;
    }

    public function listPlayers(array $filters = []): Paginate
    {
        return $this->playersRepository->listPlayers($filters);
    }

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
    ): Player

    {
        return $this->playersRepository->createPlayers(
            $contact_firstname,
            $contact_lastname,
            $phone_number,
            $email,
            $player_firstname,
            $player_lastname,
            $team_name,
            $dob,
            $agegroup,
            $description,
        );
    }

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
        string $description,
    ): bool
    {
        return $this->playersRepository->updatePlayers(
            $id,
            $contact_firstname,
            $contact_lastname,
            $phone_number,
            $email,
            $player_firstname,
            $player_lastname,
            $team_name,
            $dob,
            $agegroup,
            $description,
        );
    }

    public function retrievePlayers(int $id): Player
    {
        return $this->playersRepository->retrievePlayers($id);
    }

    public function deletePlayers(User $initiator, Player $players): bool
    {
        return $this->playersRepository->deletePlayers($players->id);
    }

    public function trashedPlayers(array $filters = []): Paginate
    {
        return $this->playersRepository->trashedPlayers($filters);
    }

    public function refundPlayer(int $id): bool
    {
        return $this->playersRepository->refundPlayer($id);
    }

    public function cancelrefPlayer(int $id): bool
    {
        return $this->playersRepository->cancelrefPlayer($id);
    }

}