<?php

namespace App\Repository\Eloquent;

use App\Models\Series;
use App\Modules\Series\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\SeriesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use DateTime;

class SeriesRepository extends BaseRepository implements seriesRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of series
     *
     * @var array $defaultSeriesListFilters
     */
    protected array $defaultSeriesListFilters = [
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
        'max_series_per_page' => self::MAX_PAGE_TEAMS,

        /**
         * Name keyword
         * When this value is null, this filter is skipped.
         */
        'name' => null,
    ];

    public function __construct(Series $series, StorageInterface $storageService)
    {
        parent::__construct($series);
        $this->storageService = $storageService;
    }

    public function listSeries(array $userFilters = []): Paginate
    {
        $series = $this->model->query();

        $filters = array_merge($this->defaultSeriesListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $series = $series->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        if (!is_null($filters['type'])) {
            $series = $series->where(function ($q) use($filters) {
                $q
                    ->where('type', 'LIKE', '%' . $filters['type'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $series = $series->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $series = $series->orderByDesc('name');
                break;

            default:
                $series = $series->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_series_per_page']) ? $series->count() : $filters['max_series_per_page'];

        return new Paginate($series, $maxPerPage, $filters['page'], 'series');
    }

    public function retrieveSeries(int $id): Series
    {
        return $this->find($id);
    }

    public function createSeries(string $name, string $type, string $description, string $address, DateTime $start, DateTime $end): Series
    {
        $series = new Series();
        $series->name = $name;
        $series->type = $type;
        $series->description = $description;
        $series->address = $address;
        $series->start = $start;
        $series->end = $end;

        return DB::transaction(function() use($series) {
            $series->save();

            return $series;
        });
    }

    public function updateSeries(int $id, string $name, string $type, string $description, string $address, DateTime $start, DateTime $end): bool
    {
        $series = $this->find($id);
        $series->name = $name;
        $series->type = $type;
        $series->description = $description;
        $series->address = $address;
        $series->start = $start;
        $series->end = $end;

        return DB::transaction(function() use($series) {

            return $series->save();
        });
    }

    public function deleteSeries(int $id): bool
    {
        $series = $this->find($id);

        return DB::transaction(function() use($series) {

            return $series->delete();
        });
    }
}
