<?php

namespace App\Repository\Eloquent;

use App\Models\Guideline;
use App\Modules\Guideline\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\GuidelineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class GuidelineRepository extends BaseRepository implements GuidelineRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of guidelines
     *
     * @var array $defaultGuidelineListFilters
     */
    protected array $defaultGuidelineListFilters = [

        /**
         * type keyword
         * this filters the guidelines with a keyword. when this value is null, this filter is skipped.
         */
        'type' => null,

        /**
         * isActive keyword
         * this filters the guidelines with a keyword. when this value is null, this filter is skipped.
         */
        'isActive' => null,

        /**
         * Search keyword
         * This filters the guidelines with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the guidelines according to this value. By default, will sort the guidelines by their creation date.
         * For the available sort values, check App\Modules\Guideline\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of guidelines to get
         */
        'page' => 1,

        /**
         * Max guideline per page
         *
         * Maximum number of guidelines shown per page. When 0 or null is passed, will get every guideline
         */
        'max_guideline_per_page' => self::MAX_PAGE_GUIDELINES ,
    ];

    public function __construct(Guideline $guideline, StorageInterface $storageService)
    {
        parent::__construct($guideline);
        $this->storageService = $storageService;
    }

    public function listGuidelines(array $userFilters = []): Paginate
    {
        $guidelines = $this->model->query();

        $filters = array_merge($this->defaultGuidelineListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        if (!is_null($filters['isActive'])) {
            $guidelines = $guidelines->where('isActive', true);
        }

        // Type Filter
        if (!is_null($filters['type'])) {
            $guidelines = $guidelines->where(function ($q) use($filters) {
                $q
                    ->where('type', 'LIKE', '%' . $filters['type'] . '%');
            });
        }

        // Search Filter
        if (!is_null($filters['q'])) {
            $guidelines = $guidelines->whereHas('user', function ($q) use ($filters) {
                $q->where('first_name', 'LIKE', '%' . $filters['q'] . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $guidelines = $guidelines->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $guidelines = $guidelines->orderByDesc('name');
                break;

            default:
                $guidelines = $guidelines->orderBy('created_at');
                break;
        }

        return new Paginate($guidelines, $filters['max_guideline_per_page'], $filters['page'], 'guidelines');
    }

    public function retrieveGuideline(int $id): guideline
    {
        return $this->find($id);
    }

    public function createGuideline(string $type, string $content): guideline
    {
        $guideline = new Guideline();
        $guideline->type = $type;
        $guideline->content = $content;

        return DB::transaction(function() use($guideline) {
            $guideline->save();

            return $guideline;
        });
    }

    public function updateGuideline(int $id, string $type, string $content): bool
    {
        $guideline = $this->find($id);
        $guideline->type = $type;
        $guideline->content = $content;
        return DB::transaction(function() use($guideline) {

            return $guideline->save();
        });
    }

    public function setActive(int $id): bool
    {
        $guideline = $this->find($id);

        $type = $guideline->type;
        $activeGuideline = Guideline::where('isActive', true)->where('type', $type)->first();

        if ($activeGuideline) {
            $activeGuideline->isActive = false;
            $activeGuideline->save();
        }

        $guideline->isActive = true;

        return DB::transaction(function() use($guideline) {
            return $guideline->save();
        });
    }

    public function deactivate(int $id): bool
    {
        $guideline = $this->find($id);
        $guideline->isActive = false;

        return DB::transaction(function() use($guideline) {
            return $guideline->save();
        });
    }

    public function deleteGuideline(int $id): bool
    {
        $guideline = $this->find($id);

        return DB::transaction(function() use($guideline) {

            return $guideline->delete();
        });
    }
}
