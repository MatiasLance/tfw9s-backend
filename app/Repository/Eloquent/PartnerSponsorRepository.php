<?php

namespace App\Repository\Eloquent;

use App\Models\PartnerSponsor;
use App\Modules\PartnerSponsor\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\PartnerSponsorRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PartnerSponsorRepository extends BaseRepository implements PartnerSponsorRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of partnerSponsors
     *
     * @var array $defaultPartnerSponsorListFilters
     */
    protected array $defaultPartnerSponsorListFilters = [
        /**
         * Search keyword
         * This filters the partnerSponsors with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the partnerSponsors according to this value. By default, will sort the partnerSponsors by their creation date.
         * For the available sort values, check App\Modules\PartnerSponsor\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of partnerSponsors to get
         */
        'page' => 1,

        /**
         * Max partnerSponsor per page
         *
         * Maximum number of partnerSponsors shown per page. When 0 or null is passed, will get every partnerSponsor
         */
        'max_partnerSponsor_per_page' => self::MAX_PAGE_PARTNERSPONSORS,
    ];

    public function __construct(PartnerSponsor $partnerSponsor, StorageInterface $storageService)
    {
        parent::__construct($partnerSponsor);
        $this->storageService = $storageService;
    }

    public function listPartnerSponsors(array $userFilters = []): Paginate
    {
        $partnerSponsors = $this->model->query();

        $filters = array_merge($this->defaultPartnerSponsorListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $partnerSponsors = $partnerSponsors->where(function ($q) use($filters) {
                $q
                    ->where('first_name', 'LIKE', '%' . $filters['q'] . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $filters['q'] . '%')
                    ->orWhere('company_name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $partnerSponsors = $partnerSponsors->orderBy('first_name');
                break;

            case Filter::SORT_Z_TO_A:
                $partnerSponsors = $partnerSponsors->orderByDesc('first_name');
                break;

            default:
                $partnerSponsors = $partnerSponsors->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_partnerSponsor_per_page']) ? $partnerSponsors->count() : $filters['max_partnerSponsor_per_page'];

        return new Paginate($partnerSponsors, $maxPerPage, $filters['page'], 'partnerSponsors');
    }

    public function retrievePartnerSponsor(int $id): PartnerSponsor
    {
        return $this->find($id);
    }

    public function createPartnerSponsor(string $company_name, string $first_name, string $last_name, string $description): partnerSponsor
    {
        $partnerSponsor = new PartnerSponsor();
        $partnerSponsor->company_name = $company_name;
        $partnerSponsor->first_name = $first_name;
        $partnerSponsor->last_name = $last_name;
        $partnerSponsor->description = $description;

        return DB::transaction(function() use($partnerSponsor) {
            $partnerSponsor->save();

            return $partnerSponsor;
        });
    }

    public function updatePartnerSponsor(int $id, string $company_name, string $first_name, string $last_name, string $description): bool
    {
        $partnerSponsor = $this->find($id);
        $partnerSponsor->company_name = $company_name;
        $partnerSponsor->first_name = $first_name;
        $partnerSponsor->last_name = $last_name;
        $partnerSponsor->description = $description;

        return DB::transaction(function() use($partnerSponsor) {

            return $partnerSponsor->save();
        });
    }

    public function deletePartnerSponsor(int $id): bool
    {
        $partnerSponsor = $this->find($id);

        return DB::transaction(function() use($partnerSponsor) {

            return $partnerSponsor->delete();
        });
    }
}
