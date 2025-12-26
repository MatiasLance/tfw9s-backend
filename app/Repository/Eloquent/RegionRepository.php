<?php

namespace App\Repository\Eloquent;

use App\Models\Category;
use App\Models\Region;
use App\Modules\Region\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\RegionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RegionRepository extends BaseRepository implements RegionRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of regions
     *
     * @var array $defaultRegionListFilters
     */
    protected array $defaultRegionListFilters = [
        /**
         * Search keyword
         * This filters the regions with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the regions according to this value. By default, will sort the regions by their creation date.
         * For the available sort values, check App\Modules\Region\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of regions to get
         */
        'page' => 1,

        /**
         * Max region per page
         *
         * Maximum number of regions shown per page. When 0 or null is passed, will get every region
         */
        'max_region_per_page' => self::MAX_PAGE_REGIONS,
    ];

    public function __construct(Region $region, StorageInterface $storageService)
    {
        parent::__construct($region);
        $this->storageService = $storageService;
    }

    public function listRegions(array $userFilters = []): Paginate
    {
        $regions = $this->model->query();

        $filters = array_merge($this->defaultRegionListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $regions = $regions->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $regions = $regions->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $regions = $regions->orderByDesc('name');
                break;

            default:
                $regions = $regions->orderBy('created_at');
                break;
        }

        return new Paginate($regions, $filters['max_region_per_page'], $filters['page'], 'regions');
    }

    public function retrieveRegion(int $id): Region
    {
        return $this->find($id);
    }

    /**
     * @todo Remove coupling to Tag model. Use tag repository or region service instead to find the tag
     */
    public function createRegion(string $name, string $description): Region
    {
        $region = new Region();
        $region->name = $name;
        $region->description = $description;

        return DB::transaction(function() use($region) {
            $region->save();

            return $region;
        });
    }

    public function updateRegion(int $id, string $name, string $description): bool
    {
        $region = $this->find($id);
        $region->name = $name;
        $region->description = $description;

        return DB::transaction(function() use($region) {

            return $region->save();
        });
    }

    public function deleteRegion(int $id): bool
    {
        $region = $this->find($id);

        return DB::transaction(function() use($region) {

            return $region->delete();
        });
    }
    public function allRegions(array $userFilters = []): Paginate
    {
        $regions = $this->model->query()->select('id','name')->orderBy('name');

        $filters = array_merge($this->defaultRegionListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        $maxPerPage = is_null($userFilters['max_region_per_page']) ? $regions->count() : $filters['max_region_per_page'];

        return new Paginate($regions, $maxPerPage, $filters['page'], 'regions');
    }
}
