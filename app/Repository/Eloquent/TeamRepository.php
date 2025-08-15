<?php

namespace App\Repository\Eloquent;

use App\Models\Team;
use App\Models\Series;
use App\Modules\Team\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TeamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Models\TeamLimit;
use App\Models\TeamRegistration;
use App\Models\DiscountCode;
use App\Modules\Payment\PaymentServiceInterface;

class TeamRepository extends BaseRepository implements teamRepositoryInterface
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
     * Default filters for retrieving list of teams
     *
     * @var array $defaultTeamListFilters
     */
    protected array $defaultTeamListFilters = [
        /**
         * Search keyword
         * This filters the teams with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the teams according to this value. By default, will sort the teams by their creation date.
         * For the available sort values, check App\Modules\Team\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of teams to get
         */
        'page' => 1,

        /**
         * Max team per page
         *
         * Maximum number of teams shown per page. When 0 or null is passed, will get every team
         */
        'max_team_per_page' => self::MAX_PAGE_TEAMS,

        /**
         * Name keyword
         * When this value is null, this filter is skipped.
         */
        'name' => null,

        /**
         * Mobile keyword
         * When this value is null, this filter is skipped.
         */
        'mobile' => null,

        /**
         * Email keyword
         * When this value is null, this filter is skipped.
         */
        'email' => null,

        /**
         * Region Filter
         * When this value is null, this filter is skipped.
         */
        'region' => null,

        /**
         * Series Filter
         * When this value is null, this filter is skipped.
         */
        'series' => null,

        /**
         * Series Type keyword
         * When this value is null, this filter is skipped.
         */
        'seriestype' => null,

        /**
         * Registered key
         * When this value is null, this filter is skipped.
         */
        'isRegistered' => null,

        /**
         * withDiscounts boolean
         * Filters out teams with discounts when true, skipped if false.
         */
        'withDiscounts' => false,


    ];

    public function __construct(Team $team, StorageInterface $storageService, PaymentServiceInterface $paymentService)
    {
        parent::__construct($team);
        $this->paymentService = $paymentService;
        $this->storageService = $storageService;
    }

    public function listTeams(array $userFilters = []): Paginate
    {
        $teams = $this->model->query();

        $filters = array_merge($this->defaultTeamListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $teams = $teams->where(function ($q) use($filters) {
                $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        if (!is_null($filters['region'])) {
            $teams->whereHas('region', function ($q) use ($filters) {
                $q->where('id', $filters['region']);
            });
        }
        
        if (!is_null($filters['series'])) {
            $teams->whereHas('series', function ($q) use ($filters) {
                $q->where('id', $filters['series']);
            });
        }

        if (!is_null($filters['seriestype'])) {
            // If it's numeric, filter by ID
        if (is_numeric($filters['seriestype'])) {
            $teams->where('series_id', $filters['seriestype']);
            } 
            // Otherwise filter by type
            else {
            $teams->whereHas('series', function ($q) use ($filters) {
            $q->where('type', 'LIKE', '%' . $filters['seriestype'] . '%');
            });
        }
        }

        if (!is_null($filters['isRegistered'])) {
            $teams->whereHas('registration', function ($q) use ($filters) {
                $q->whereNotNull('transaction_id');
            });
        }

        if ($filters['withDiscounts']) {

            $teams = $teams->with(['registration', 'discountCode']);
            $teams = $teams->whereHas('discountCode');

        }else{
            $teams = $teams->with(['registration', 'discountCode']);
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $teams = $teams->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $teams = $teams->orderByDesc('name');
                break;

            case Filter::SORT_LATEST:
                $teams = $teams->orderByDesc('updated_at');
                break;


            default:
                $teams = $teams->orderBy('created_at');
                break;
        }
        $maxPerPage = is_null($userFilters['max_team_per_page']) ? $teams->count() : $filters['max_team_per_page'];

        return new Paginate($teams, $maxPerPage, $filters['page'], 'teams');
    }

    public function retrieveTeam(int $id): Team
    {
        return Team::find($id);
    }

    public function createTeam(string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media, string $type, int $region_id, int $player_limit, int $discount_id): Team
    {
        $team = new Team();
        $team->discount_codes_id = $discount_id;
        $team->name = $name;
        $team->agegroup_id = $agegroup_id;
        $team->series_id = $series_id;
        $team->coach_name = $coach['name'];
        $team->coach_mobile = $coach['mobile'];
        $team->coach_email = $coach['email'];
        $team->manager_name = $manager['name'];
        $team->manager_mobile = $manager['mobile'];
        $team->manager_email = $manager['email'];
        $team->region_id = $region_id;
        $team->player_limit = $player_limit;

        $discountCodes = DiscountCode::where('id', $discount_id)->get();

        $teamLimit = null;
        if (in_array($type, ['tournament', 'cost'])) {
            $teamLimit = TeamLimit::where('series_id', $series_id)
                ->whereHas('ageGroups', function ($query) use ($agegroup_id) {
                    $query->where('agegroup_id', $agegroup_id);
                })
                ->first();
    
            if (!$teamLimit) {
                throw new \Exception("Team limit not found for this series and age group.");
            }
        }    

        return DB::transaction(function() use($team, $media, $teamLimit, $discountCodes) {
            $team->save();

            foreach ($media as $file) {
                if (!is_null($file)) {

                    $teamImage = $this->storageService->store($file);
                    $team->media()->save($teamImage);
                }
            }

            if ($teamLimit) {
                $teamLimit->teamcount += 1;
                $teamLimit->save();
            }

            // Update discount code usage limit
            foreach($discountCodes as $discountCode){
                if($discountCode->usage_count < $discountCode->usage_limit){
                    $discountCode->usage_count += 1;
                    $discountCode->save();
                }else{
                    throw new \Exception("Discount code usage limit reached.");
                }
            }

            return $team;
        });
    }

    public function updateTeam(int $id, string $name, int $agegroup_id, int $series_id, array $coach, array $manager, ?array $media, int $region_id, $player_limit, int $discount_id): bool
    {
        $team = $this->find($id);
        $oldAgegroupId = $team->agegroup_id;
        $oldSeriesId = $team->series_id;
        $oldRegionId = $team->region_id;

        $team->discount_codes_id = $discount_id;
        $team->name = $name;
        $team->agegroup_id = $agegroup_id;
        $team->series_id = $series_id;
        $team->coach_name = $coach['name'];
        $team->coach_mobile = $coach['mobile'];
        $team->coach_email = $coach['email'];
        $team->manager_name = $manager['name'];
        $team->manager_mobile = $manager['mobile'];
        $team->manager_email = $manager['email'];
        $team->region_id = $region_id;
        $team->player_limit = $player_limit;

        $discountCodes = DiscountCode::where('id', $discount_id)->get();
        

        return DB::transaction(function() use($team, $media, $oldAgegroupId, $oldSeriesId, $oldRegionId, $discountCodes) {
            if (!is_null($media)) {
                $newMedia = array_filter($media, function ($file) {
                    return $file instanceof UploadedFile;
                });

                $oldMedia = array_filter($media, function ($file) {
                    return !$file instanceof UploadedFile;
                });

                foreach ($team->media as $existingMedia) {
                    if (
                        $existingMedia->path !== 'media/default/' . self::PLACEHOLDER_IMAGE &&
                            !in_array($existingMedia->hash, $oldMedia)
                    ) {
                        $this->storageService->delete($existingMedia);
                        $existingMedia->delete();
                    }
                }

                foreach ($newMedia as $newFile) {
                    $teamImage = $this->storageService->store($newFile);
                    $team->media()->save($teamImage);
                }
            }

            // Update team limit
            if ($team->agegroup_id != $oldAgegroupId || $team->series_id != $oldSeriesId) {
                // Decrement the old team limit
                $oldTeamLimit = TeamLimit::where('series_id', $oldSeriesId)
                    ->whereHas('ageGroups', function ($query) use ($oldAgegroupId) {
                        $query->where('agegroup_id', $oldAgegroupId);
                    })
                    ->first();

                if ($oldTeamLimit) {
                    $oldTeamLimit->teamcount -= 1;
                    $oldTeamLimit->save();
                }

                // Increment the new team limit
                $newTeamLimit = TeamLimit::where('series_id', $team->series_id)
                    ->whereHas('ageGroups', function ($query) use ($team) {
                        $query->where('agegroup_id', $team->agegroup_id);
                    })
                    ->first();

                if ($newTeamLimit) {
                    $newTeamLimit->teamcount += 1;
                    $newTeamLimit->save();
                }
            }

            return $team->save();
        });
    }

    public function deleteTeam(int $id): bool
    {
        $team = $this->find($id);

        return DB::transaction(function() use($team) {
            // Decrement the team limit
            $teamLimit = TeamLimit::where('series_id', $team->series_id)
                ->whereHas('ageGroups', function ($query) use ($team) {
                    $query->where('agegroup_id', $team->agegroup_id);
                })
                ->first();

            if ($teamLimit) {
                $teamLimit->teamcount -= 1;
                $teamLimit->save();
            }

            return $team->delete();
        });
    }

    public function allTeams(array $userFilters = []): Paginate
    {
        $teams = $this->model->query()->select('id', 'name', 'event_id')->orderBy('name');

        $filters = array_merge($this->defaultTeamListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        $maxPerPage = is_null($userFilters['max_team_per_page']) ? $teams->count() : $filters['max_team_per_page'];

        return new Paginate($teams, $maxPerPage, $filters['page'], 'teams');
    }

    public function trashedTeams(array $userFilters = []): Paginate
    {

        $teams = $this->model->onlyTrashed()->newQuery();

        $filters = array_merge($this->defaultTeamListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        $teams->whereHas('registration', function ($q) use ($filters) {
            $q->whereNotNull('refund_id');
        });

        // Search Filter
        if (!is_null($filters['q'])) {
            $teams = $teams->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        if (!is_null($filters['seriestype'])) {
            $teams->whereHas('series', function ($q) use ($filters) {
                $q->where('type', 'LIKE', '%' . $filters['seriestype'] . '%');
            });
        }
        

        $teams = $teams->with('registration');

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $teams = $teams->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $teams = $teams->orderByDesc('name');
                break;

            case Filter::SORT_LATEST:
                $teams = $teams->orderByDesc('updated_at');
                break;


            default:
                $teams = $teams->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_team_per_page']) ? $teams->count() : $filters['max_team_per_page'];

        return new Paginate($teams, $maxPerPage, $filters['page'], 'teams');
    }

    public function refundTeam(int $id, int $amount): bool
    {
        $team = $this->find($id);

        return DB::transaction(function() use($team, $amount) {

            $teamregistration = TeamRegistration::find($team->registration_id);

            $transaction_id = $team->registration->transaction_id;
            $method = $team->registration->payment_gateway;

            $refund = $this->paymentService->registrationRefund($method, $transaction_id, $amount);  

            if ($refund === null) {
                throw new \Exception("Refund failed, refund ID is null.");
            }

            $teamregistration->refund_id = $refund;
            $teamregistration->refund = $amount; 
            $teamregistration->save();

            // Decrement the team limit
            $teamLimit = TeamLimit::where('series_id', $team->series_id)
                ->whereHas('ageGroups', function ($query) use ($team) {
                    $query->where('agegroup_id', $team->agegroup_id);
                })
                ->first();

            if ($teamLimit) {
                $teamLimit->teamcount -= 1;
                $teamLimit->save();
            }

            return $team->delete();
        });
    }

    public function cancelrefTeam(int $id): bool
    {
        $team = Team::withTrashed()->find($id);

        return DB::transaction(function() use($team) {

            $teamregistration = TeamRegistration::find($team->registration_id);

            $method = $team->registration->payment_gateway;
            $refund_id = $team->registration->refund_id;

            $cancel = $this->paymentService->cancelRefund($method, $refund_id);  

            $teamregistration->refund_id = $cancel; 
            $teamregistration->save();

            // Decrement the team limit
            $teamLimit = TeamLimit::where('series_id', $team->series_id)
                ->whereHas('ageGroups', function ($query) use ($team) {
                    $query->where('agegroup_id', $team->agegroup_id);
                })
                ->first();

            if ($teamLimit) {
                $teamLimit->teamcount += 1;
                $teamLimit->save();
            }

            return $team->restore();
        });
    }

    public function generateUrl(int $id): string
    {
        $team = Team::withTrashed()->find($id);
        $series = Series::withTrashed()->find($team->series_id);

        return DB::transaction(function() use($team, $series) {

            $payload = [];
            $payload['series'] = $series->id;
            $payload['team'] = $team->id;
            $encryptedToken = encrypt($payload);
    
            $link = url('/register?id=' . $series->id . '&series=' . urlencode($series->name) . '&price=' . $series->price . '&token=' . $encryptedToken);

            return $link;
        });
    }
}