<?php

namespace App\Repository\Eloquent;

use App\Models\Player;
use App\Modules\Players\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\PlayersRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\IndividualRegistration;
use App\Modules\Payment\PaymentServiceInterface;
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
    ];

    public function __construct(Player $players, PaymentServiceInterface $paymentService)
    {
        parent::__construct($players);
        $this->paymentService = $paymentService;
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
        $players = new Player();
        $players->contact_firstname = $contact_firstname;
        $players->contact_lastname = $contact_lastname;
        $players->phone_number = $phone_number;
        $players->email = $email;
        $players->player_firstname = $player_firstname;
        $players->player_lastname = $player_lastname;
        $players->team_name = $team_name;
        $players->dob = $dob;
        $players->agegroup_id = $agegroup;
        $players->description = $description;

        return DB::transaction(function() use($players) {
            $players->save();

            return $players;
        });
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
        string $description
    ): bool

    {
        $players = $this->find($id);
        $players->contact_firstname = $contact_firstname;
        $players->contact_lastname = $contact_lastname;
        $players->phone_number = $phone_number;
        $players->email = $email;
        $players->player_firstname = $player_firstname;
        $players->player_lastname = $player_lastname;
        $players->team_name = $team_name;
        $players->dob = $dob;
        $players->agegroup_id = $agegroup;
        $players->description = $description;

        return DB::transaction(function() use($players) {
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

    public function refundPlayer(int $id): bool
    {
        $player = $this->find($id);

        return DB::transaction(function() use($player) {

            $playerregistration = IndividualRegistration::find($player->registration_id);

            $transaction_id = $player->registration->transaction_id;
            $amount = $player->registration->price;
            $method = $player->registration->payment_gateway;

            $refund = $this->paymentService->registrationRefund($method, $transaction_id, $amount);  

            $playerregistration->refund_id = $refund; 
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

            if ($refund === null) {
                throw new \Exception("Refund failed, refund ID is null.");
            }

            $cancel = $this->paymentService->cancelRefund($method, $refund_id);
            dd($cancel);

            $playerregistration->refund_id = $cancel; 
            $playerregistration->save();

            return $player->restore();
        });
    }

}
