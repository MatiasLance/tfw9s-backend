<?php

namespace App\Http\Controllers;

use App\Modules\Http\Message;
use App\Modules\Players\PlayersServiceInterface;
use Illuminate\Http\Request;
use App\Models\Player;
use App\Modules\Storage\StorageInterface;
use Illuminate\Support\Facades\DB;
use DateTime;

class PlayersController extends Controller
{
    protected PlayersServiceInterface $playersService;

    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    public function __construct(PlayersServiceInterface $playersService, StorageInterface $storageService)
    {
        $this->playersService = $playersService;
        $this->storageService = $storageService;
    }

    public function list(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $type = $request->query('type', null);
        $agegroup = $request->query('agegroup', null);
        $team = $request->query('team', null);
        $withPlayers = $request->query('withPlayers', null);
        $maxPlayersPerPage = $request->query('maxPlayersPerPage', null);
        $isRegistered = $request->query('isRegistered', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'type' => $type,
            'agegroup' => $agegroup,
            'team' => $team,
            'withPlayers' => $withPlayers,
            'max_players_per_page' => $maxPlayersPerPage,
            'isRegistered' => $isRegistered,
        ];

        $players = $this->playersService->listPlayers($filter);

        $message->setContent(200, 'Players retrieved', '', $players->toArray());

        return $message->render();
    }

    public function store(Request $request, Message $message)
    {
        $contact_firstname = $request->input('contact_firstname');
        $contact_lastname = $request->input('contact_lastname');
        $phone_number = $request->input('phone_number');
        $email = $request->input('email');
        $player_firstname = $request->input('player_firstname');
        $player_lastname = $request->input('player_lastname');
        $team_id = $request->input('teamID');
        $dobstring = $request->input('dob');
        $agegroup_id = $request->input('agegroupID');
        $description = $request->input('description') ?? '';
        $series_id = $request->input('seriesID');
        $media = $request->file('photo') ?? [];

        $dob = new DateTime($dobstring);

        $players = $this->playersService->createPlayers(
            $contact_firstname,
            $contact_lastname,
            $phone_number,
            $email,
            $player_firstname,
            $player_lastname,
            $team_id,
            $dob,
            $agegroup_id,
            $description,
            $series_id,
            $media
        );

        if ($players instanceof Player) {
            $message->setContent(201, 'Player created', '', [
                'players' => $players
            ]);
        } else {
            $message->setContent(400, 'Player not created');
        }

        return $message->render();
    }

    public function update(Request $request, Message $message, int $id)
    {
        $contact_firstname = $request->input('contact_firstname');
        $contact_lastname = $request->input('contact_lastname');
        $phone_number = $request->input('phone_number');
        $email = $request->input('email');
        $player_firstname = $request->input('player_firstname');
        $player_lastname = $request->input('player_lastname');
        $team_id = $request->input('teamID');
        $dobstring = $request->input('dob');
        $agegroup_id = $request->input('agegroupID');
        $description = $request->input('description') ?? '';
        $series_id = $request->input('seriesID');
        $newPhoto = $request->file('photo') ?? [];

        $existingPhoto = $request->input('photo') ?? [];
        $newPhotoCount = count($newPhoto);
        $existingPhotoCount = count($existingPhoto);

        if (
            $request->has('photo') &&
            (
                $newPhotoCount > 0 ||
                $existingPhotoCount > 0
            )
        ) {
            foreach ($existingPhoto as $existingPhotoHash) {
                array_push($newPhoto, $existingPhotoHash);
            }
            $photo = $newPhoto;
        } else {
            $photo = null;
        }

        $dob = new DateTime($dobstring);

        $isSuccess = $this->playersService->updatePlayers(
            $id,
            $contact_firstname,
            $contact_lastname,
            $phone_number,
            $email,
            $player_firstname,
            $player_lastname,
            $team_id,
            $dob,
            $agegroup_id,
            $description,
            $series_id,
            $photo
        );

        if ($isSuccess) {
            $message->setContent(200, 'Players updated');
        } else {
            $message->setContent(400, 'Players not updated');
        }

        return $message->render();
    }

    public function retrieve(Message $message, int $id)
    {
        $players = $this->playersService->retrievePlayers($id);

        $message->setContent(200, 'Players retrieved', '', [
            'players' => $players
        ]);

        return $message->render();
    }

    public function delete(Request $request, Message $message, int $id)
    {

        $user = $request->user();
        $players = $this->playersService->retrievePlayers($id);

        $isSuccess = $this->playersService->deletePlayers($user, $players);

        if ($isSuccess) {
            $message->setContent(200, 'Players deleted');
        } else {
            $message->setContent(400, 'Players not updated');
        }

        return $message->render();
    }

    public function trashed(Request $request, Message $message)
    {
        $query = $request->query('q', null);
        $sort = $request->query('sort', null);
        $page = $request->query('page', null);
        $type = $request->query('type', null);
        $agegroup = $request->query('agegroup', null);
        $withPlayers = $request->query('withPlayers', null);
        $maxPlayersPerPage = $request->query('maxPlayersPerPage', null);

        $filter = [
            'q' => $query,
            'sort' => $sort,
            'page' => $page,
            'type' => $type,
            'agegroup' => $agegroup,
            'withPlayers' => $withPlayers,
            'max_players_per_page' => $maxPlayersPerPage,
        ];

        $players = $this->playersService->trashedPlayers($filter);

        $message->setContent(200, 'Players retrieved', '', $players->toArray());

        return $message->render();
    }

    public function refund(Request $request, Message $message, int $id)
    {
        $amount = $request->input('amount', null);
        $refund = $this->playersService->refundPlayer($id, $amount);

        $message->setContent(200, 'Player refunded', '', [
            'refund' => $refund
        ]);

        return $message->render();
    }

    public function cancelref(Message $message, int $id)
    {
        $cancel = $this->playersService->cancelrefPlayer($id);

        $message->setContent(200, 'Refund canceled', '', [
            'canceled' => $cancel
        ]);

        return $message->render();
    }

    public function savemedia(Message $message, Request $request, int $id)
    {
        $media = $request->file('photo');

        $result = DB::transaction(function () use ($id, $media) {
            if (!$media) {
                return 'Missing `photo` file.';
            }

            $player = Player::find($id);

            if (!$player) {
                return 'Player not found.';
            }

            $mediaData = $this->storageService->store($media);

            if (!$mediaData) {
                return 'Failed to store media.';
            }

            // Keep one image only for the target
            foreach ($player->media as $existingMedia) {
                $this->storageService->delete($existingMedia);
                $existingMedia->delete();
            }

            $saved = $player->media()->save($mediaData);


            if (!$saved) {
                return 'Failed to save media to target.';
            }

            return 'success';
        });

        if ($result === 'success') {
            $message->setContent(200, 'Player image uploaded!');
        } else {
            $message->setContent(400, $result);
        }
    }

    public function suggestNames(Request $request, Message $message)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $query = $request->input('q');

        $suggestions = $this->playersService->suggestNames($query, 10);

        $message->setContent(200, 'Suggestions', '', [
            'suggestions' => $suggestions,
        ]);

        return $message->render();

    }
}
