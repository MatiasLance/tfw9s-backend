<?php

namespace App\Repository\Eloquent;

use App\Models\Player;
use App\Modules\Players\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\PlayersRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\IndividualRegistration;
use App\Modules\Payment\PaymentServiceInterface;
use Illuminate\Http\UploadedFile;
use DateTime;

class PlayersRepository extends BaseRepository implements PlayersRepositoryInterface
{
     /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Payment service
     *
     * @var PaymentServiceInterface $paymentService
     */
    protected PaymentServiceInterface $paymentService;

    /**
     * Default filters for retrieving list of series
     *
     * @var array $defaultPlayersListFilters
     */
    protected array $defaultPlayersListFilters = [
        /**
         * Search keyword
         * This filters the series with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Type filter
         * This filters the series by type. When this value is null, this filter is skipped.
         */
        'type' => null,

        /**
         * AgeGroup filter
         * This filters the players by agegroup. When this value is null, this filter is skipped.
         */
        'agegroup' => null,

        /**
         * Team filter
         * This filters the players by team. When this value is null, this filter is skipped.
         */
        'team' => null,

        /**
         * withFixing filter
         * This filters the series by type. When this value is null, this filter is skipped.
         */
        'withFixing' => null,

        /**
         * Sort
         * Sorts the series according to this value. By default, will sort the series by their creation date.
         * For the available sort values, check App\Modules\Series\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of series to get
         */
        'page' => 1,

        /**
         * Max series per page
         *
         * Maximum number of series shown per page. When 0 or null is passed, will get every series
         */
        'max_players_per_page' => self::MAX_PAGE_TEAMS,

        /**
         * Name keyword
         * When this value is null, this filter is skipped.
         */
        'name' => null,

        /**
         * Registered key
         * When this value is null, this filter is skipped.
         */
        'isRegistered' => null,
    ];

    public function __construct(Player $players, PaymentServiceInterface $paymentService, StorageInterface $storageService)
    {
        parent::__construct($players);
        $this->paymentService = $paymentService;
        $this->storageService = $storageService;
    }

    public function listPlayers(array $playersFilters = []): Paginate
    {
        $players = $this->model->query();


        $filters = array_merge($this->defaultPlayersListFilters, array_filter($playersFilters, fn($f) => !is_null($f)));

        // if (!is_null($filters['withFixing'])) {
        //    $players = $players->has('event');
        // }

        // Search Filter

        if (!is_null($filters['q'])) {
            $players = $players->where('contact_firstname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('contact_lastname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('phone_number', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('email', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('player_firstname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('player_lastname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('team_id', 'like', '%' . $filters['q'] . '%');
        }    

        if (!is_null($filters['type'])) {
            $players = $players->where('agegroup', $filters['type']);
        }

        if (is_null($filters['withFixing'])) {
            $players = $players->with('registration');
        }

        if (!is_null($filters['agegroup'])) {
            $players = $players->where('agegroup_id', $filters['agegroup']);
        }

        if (!is_null($filters['team'])) {
            $players->whereHas('team', function ($q) use ($filters) {
                $q->where('id', $filters['team']);
            });
        }

        if (!is_null($filters['isRegistered'])) {
            $players->whereHas('registration', function ($q) use ($filters) {
                $q->whereNotNull('transaction_id');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $players = $players->orderBy('contact_firstname');
                break;
            case Filter::SORT_Z_TO_A:
                $players = $players->orderByDesc('contact_firstname');
                break;
            case Filter::SORT_LATEST:
                $players = $players->orderByDesc('updated_at');
                break;
            default:
                $players = $players->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($filters['max_players_per_page']) ? $players->count() : $filters['max_players_per_page'];

        return new Paginate($players, $maxPerPage, $filters['page'], 'players');
    }

    public function createPlayers(
        ?string $contact_firstname,
        ?string $contact_lastname,
        ?string $phone_number,
        ?string $email,
        string $player_firstname,
        string $player_lastname,
        int $team_id,
        DateTime $dob,
        int $agegroup_id,
        ?string $description,
        int $series_id,
        ?array $media
    ): Player

    {
        $players = new Player();
        $players->contact_firstname = $contact_firstname ?? '';
        $players->contact_lastname = $contact_lastname ?? '';
        $players->phone_number = $phone_number ?? '';
        $players->email = $email ?? '';
        $players->player_firstname = $player_firstname;
        $players->player_lastname = $player_lastname;
        $players->team_id = $team_id;
        $players->dob = $dob;
        $players->agegroup_id = $agegroup_id;
        $players->description = $description;
        $players->series_id = $series_id;

        return DB::transaction(function() use($players, $media) {
            $players->save();

            foreach ($media as $file) {
                if (!is_null($file)) {

                    $Image = $this->storageService->store($file);
                    $players->media()->save($Image);
                }
              }

            return $players;
        });
    }

    public function updatePlayers(
        int $id,
        ?string $contact_firstname,
        ?string $contact_lastname,
        ?string $phone_number,
        ?string $email,
        string $player_firstname,
        string $player_lastname,
        int $team_id,
        DateTime $dob,
        int $agegroup_id,
        ?string $description,
        int $series_id,
        ?array $media
    ): bool

    {
        $players = $this->find($id);
        $players->contact_firstname = $contact_firstname ?? '';
        $players->contact_lastname = $contact_lastname ?? '';
        $players->phone_number = $phone_number ?? '';
        $players->email = $email ?? '';
        $players->player_firstname = $player_firstname;
        $players->player_lastname = $player_lastname;
        $players->team_id = $team_id;
        $players->dob = $dob;
        $players->agegroup_id = $agegroup_id;
        $players->description = $description;
        $players->series_id = $series_id;

        return DB::transaction(function() use($players, $media) {
            if (!is_null($media)) {
                $newMedia = array_filter($media, function ($file) {
                    return $file instanceof UploadedFile;
                });

                $oldMedia = array_filter($media, function ($file) {
                    return !$file instanceof UploadedFile;
                });

                foreach ($players->media as $existingMedia) {
                    if (
                        $existingMedia->path !== 'media/default/' . self::PLACEHOLDER_IMAGE &&
                        !in_array($existingMedia->hash, $oldMedia)
                    ) {
                        $this->storageService->delete($existingMedia);
                        $existingMedia->delete();
                    }
                }

                foreach ($newMedia as $newFile) {
                    $playerPhoto = $this->storageService->store($newFile);
                    $players->media()->save($playerPhoto);
                }
            } else {
                foreach ($players->media as $existingMedia) {
                    $this->storageService->delete($existingMedia);
                    $existingMedia->delete();
                }
            }

            return $players->save();
        });
    }

    public function retrievePlayers(int $id): Player
    {
        return Player::find($id);
    }

    public function deletePlayers(int $id): bool
    {
        $players = $this->find($id);

        return DB::transaction(function() use($players) {
            return $players->delete();
        });
    }

    public function trashedPlayers(array $playersFilters = []): Paginate
    {
        $players = $this->model->onlyTrashed()->newQuery();


        $filters = array_merge($this->defaultPlayersListFilters, array_filter($playersFilters, fn($f) => !is_null($f)));

        $players->whereHas('registration', function ($q) use ($filters) {
            $q->whereNotNull('refund_id');
        });

        if (!is_null($filters['q'])) {
            $players = $players->where('contact_firstname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('contact_lastname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('phone_number', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('email', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('player_firstname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('player_lastname', 'like', '%' . $filters['q'] . '%')
                     ->orWhere('team_name', 'like', '%' . $filters['q'] . '%');
        }    

        if (!is_null($filters['type'])) {
            $players = $players->where('agegroup', $filters['type']);
        }

        if (is_null($filters['withFixing'])) {
            $players = $players->with('registration');
        }

        if (!is_null($filters['agegroup'])) {
            $players = $players->where('agegroup', $filters['agegroup']);
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $players = $players->orderBy('contact_firstname');
                break;
            case Filter::SORT_Z_TO_A:
                $players = $players->orderByDesc('contact_firstname');
                break;
            case Filter::SORT_LATEST:
                $players = $players->orderByDesc('updated_at');
                break;
            default:
                $players = $players->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($filters['max_players_per_page']) ? $players->count() : $filters['max_players_per_page'];

        return new Paginate($players, $maxPerPage, $filters['page'], 'players');
    }

    public function refundPlayer(int $id, int $amount): bool
    {
        $player = $this->find($id);

        return DB::transaction(function() use($player, $amount) {

            $playerregistration = IndividualRegistration::find($player->registration_id);

            $transaction_id = $player->registration->transaction_id;
            $method = $player->registration->payment_gateway;

            $refund = $this->paymentService->registrationRefund($method, $transaction_id, $amount);  

            $playerregistration->refund_id = $refund; 
            $playerregistration->refund = $amount; 
            $playerregistration->save();

            return $player->delete();
        });
    }

    public function cancelrefPlayer(int $id): bool
    {
        $player = Player::withTrashed()->find($id);

        return DB::transaction(function() use($player) {

            $playerregistration = IndividualRegistration::find($player->registration_id);

            $method = $player->registration->payment_gateway;
            $refund_id = $player->registration->refund_id;

            if ($refund_id === null) {
                throw new \Exception("Refund failed, refund ID is null.");
            }

            $cancel = $this->paymentService->cancelRefund($method, $refund_id);

            $playerregistration->refund_id = $cancel; 
            $playerregistration->save();

            return $player->restore();
        });
    }

    public function suggestNames(string $query, int $limit = 10): Collection
    {
        $query = preg_replace('/[^a-zA-Z\-\'\s]/', '', trim($query));
        if (empty($query)) {
            return collect();
        }

        $tokens = explode(' ', $query);
        $firstToken = $tokens[0];
        $secondToken = $tokens[1] ?? null;

        $players = $this->model->query()
            ->select('id', 'team_id', 'agegroup_id', 'series_id', 'contact_firstname', 'contact_lastname', 'phone_number', 'email', 'player_firstname', 'player_lastname', 'dob')
            ->where(function ($q) use ($firstToken, $secondToken) {
                if ($secondToken) {
                    $q->where('player_firstname', 'LIKE', $firstToken.'%')
                    ->where('player_lastname', 'LIKE', $secondToken.'%');
                } else {
                    $q->where('player_firstname', 'LIKE', $firstToken.'%')
                    ->orWhere('player_lastname', 'LIKE', $firstToken.'%');
                }
            })
            ->orderByRaw('CHAR_LENGTH(player_firstname) ASC')
            ->limit($limit)
            ->get();

        return $players->map(fn($p) => [
            'id'    => $p->id,
            'team_id' => $p->team_id,
            'ageGroup_id' => $p->agegroup_id,
            'series_id' => $p->series_id,
            'parent_first_name' => $p->contact_firstname,
            'parent_last_name' => $p->contact_lastname,
            'first_name' => $p->player_firstname,
            'last_name' => $p->player_lastname,
            'phone_number' => $p->phone_number,
            'email' => $p->email,
            'date_of_birth' => $p->dob,
            'name'  => $p->player_firstname . ' ' . $p->player_lastname,
        ]);
    }

}
