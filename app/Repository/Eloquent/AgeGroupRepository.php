<?php

namespace App\Repository\Eloquent;

use App\Models\AgeGroup;
use App\Models\Series;
use App\Models\TeamLimit;
use App\Modules\AgeGroup\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\AgeGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class AgeGroupRepository extends BaseRepository implements AgeGroupRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of ageGroups
     *
     * @var array $defaultAgeGroupListFilters
     */
    protected array $defaultAgeGroupListFilters = [
        /**
         * Search keyword
         * This filters the ageGroups with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the ageGroups according to this value. By default, will sort the ageGroups by their creation date.
         * For the available sort values, check App\Modules\AgeGroup\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of ageGroups to get
         */
        'page' => 1,

        /**
         * Max ageGroup per page
         *
         * Maximum number of ageGroups shown per page. When 0 or null is passed, will get every ageGroup
         */
        'max_age_group_per_page' => self::MAX_PAGE_AGEGROUPS,
    ];

    public function __construct(AgeGroup $ageGroup, StorageInterface $storageService)
    {
        parent::__construct($ageGroup);
        $this->storageService = $storageService;
    }

    public function listAgeGroup(array $userFilters = []): Paginate
    {
        $ageGroups = $this->model->query();

        $filters = array_merge($this->defaultAgeGroupListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $ageGroups = $ageGroups->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $ageGroups = $ageGroups->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $ageGroups = $ageGroups->orderByDesc('name');
                break;

            default:
                $ageGroups = $ageGroups->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_age_group_per_page']) ? $ageGroups->count() : $filters['max_age_group_per_page'];

        return new Paginate($ageGroups, $maxPerPage, $filters['page'], 'ageGroups');
    }

    public function retrieveAgeGroup(int $id): ageGroup
    {
        return $this->find($id);
    }

    public function createAgeGroup(string $name, int $min_age, int $max_age): AgeGroup
    {
        $ageGroup = new AgeGroup(); 
        $ageGroup->name = $name;
        $ageGroup->min_age = $min_age;
        $ageGroup->max_age = $max_age;

        return DB::transaction(function() use($ageGroup) {
            $ageGroup->save();

            $seriesList = Series::all();

            foreach ($seriesList as $series) {
                $teamLimit = new TeamLimit();
                $teamLimit->series_id = $series->id;
                $teamLimit->save();

            $teamLimit->ageGroups()->attach($ageGroup->id);
            }

            return $ageGroup;
        });
    }

    public function updateAgeGroup(int $id, string $name, int $min_age, int $max_age): bool
    {
        $ageGroup = $this->find($id);
        $ageGroup->name = $name;
        $ageGroup->min_age = $min_age;
        $ageGroup->max_age = $max_age;

        return DB::transaction(function() use($ageGroup) {

            return $ageGroup->save();
        });
    }

    public function deleteAgeGroup(int $id): bool
    {
        $ageGroup = $this->find($id);

        return DB::transaction(function() use($ageGroup) {

            return $ageGroup->delete();
        });
    }
}
