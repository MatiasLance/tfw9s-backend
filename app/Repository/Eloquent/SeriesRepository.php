<?php

namespace App\Repository\Eloquent;

use App\Models\Series;
use App\Modules\TeamLimit\TeamLimitServiceInterface;
use App\Modules\Series\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\SeriesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Modules\Mail\MailService;

class SeriesRepository extends BaseRepository implements seriesRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    protected TeamLimitServiceInterface $teamLimitService;

    protected MailService $mailService;

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
         * Event Date keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'event_date' => null,

        /**
         * Type filter
         * This filters the series by type. When this value is null, this filter is skipped.
         */
        'type' => null,

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
        'max_series_per_page' => self::MAX_PAGE_TEAMS,

        /**
         * Name keyword
         * When this value is null, this filter is skipped.
         */
        'name' => null,

        /**
         * is_paused filter
         * This filters the series by type. When this value is null, this filter is skipped.
         */
        'is_paused' => null,
    ];

    public function __construct(Series $series, StorageInterface $storageService, TeamLimitServiceInterface $teamLimitService, MailService $mailService)
    {
        parent::__construct($series);
        $this->storageService = $storageService;
        $this->teamLimitService = $teamLimitService;
        $this->mailService = $mailService;
    }

    public function listSeries(array $userFilters = []): Paginate
    {
        $series = $this->model->query()->with('ageGroup');

        $filters = array_merge($this->defaultSeriesListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // if (!is_null($filters['withFixing'])) {
        //    $series = $series->has('event');
        // }

        // Search Filter
        if (!is_null($filters['q'])) {
            $series = $series->where(function ($q) use($filters) {
                $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        if (!is_null($filters['type'])) {
            $series = $series->where(function ($q) use($filters) {
                $q->where('type', 'LIKE', '%' . $filters['type'] . '%');
            });
        }

        if (!is_null($filters['event_date'])) {
            $series = $series->where(function ($q) use($filters) {
                $q->where('start', 'LIKE', '%' . $filters['event_date'] . '%');
            });
        }

        if (!is_null($filters['is_paused'])) {
            $series = $series->where('is_paused', $filters['is_paused']);
        }

        switch ($filters['sort']) {
            case Filter::SORT_IS_PAUSED:
                $series = $series->orderBy('is_paused');
                break;
            case Filter::SORT_A_TO_Z:
                $series = $series->orderBy('name');
                break;
            case Filter::SORT_Z_TO_A:
                $series = $series->orderByDesc('name');
                break;
            case Filter::SORT_START_DATE:
                $series = $series->orderByDesc('start');
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
        $series = Series::with('ageGroup')->where('id', $id)->first();
        return $series;
    }    

    public function createSeries(string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media): Series
    {
        $series = new Series();
        $series->name = $name;
        $series->type = $type;
        $series->description = $description;
        $series->address = $address;
        $series->start = $start;
        $series->end = $end;
        $series->price = $price;

        return DB::transaction(function() use($series, $media, $type) {
            $series->save();

            if ($type != 'weekly') {
                $this->teamLimitService->createTeamLimit($series->id);
            }

            foreach ($media as $file) {
                if (!is_null($file)) {

                    $Image = $this->storageService->store($file);
                    $series->media()->save($Image);
                }
            }

            return $series;
        });
    }

    public function updateSeries(int $id, string $name, string $type, string $description, string $address, DateTime $start, DateTime $end, float $price, ?array $media): bool
    {
        $series = $this->find($id);
        $series->name = $name;
        $series->type = $type;
        $series->description = $description;
        $series->address = $address;
        $series->start = $start;
        $series->end = $end;
        $series->price = $price;

        return DB::transaction(function() use($series, $media) {

            if (!is_null($media)) {
                $newMedia = array_filter($media, function ($file) {
                    return $file instanceof UploadedFile;
                });

                $oldMedia = array_filter($media, function ($file) {
                    return !$file instanceof UploadedFile;
                });

                foreach ($series->media as $existingMedia) {
                    if (
                        $existingMedia->path !== 'media/default/' . self::PLACEHOLDER_IMAGE &&
                        !in_array($existingMedia->hash, $oldMedia)
                    ) {
                        $this->storageService->delete($existingMedia);
                        $existingMedia->delete();
                    }
                }

                foreach ($newMedia as $newFile) {

                    $Image = $this->storageService->store($newFile);
                    $series->media()->save($Image);
                }
            }

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

    public function resumeSeries(int $id): bool
    {
        $series = $this->find($id);
        $series->is_paused = false;

        return DB::transaction(function() use($series) {
            return $series->save();
        });
    }

    public function pauseSeries(int $id): bool
    {
        $series = $this->find($id);
        $series->is_paused = true;

        return DB::transaction(function() use($series) {
            return $series->save();
        });
    }

    public function editThumbnail(?array $media): bool
    {
        $series = Series::all();

        return DB::transaction(function() use($series, $media) {
            foreach ($series as $series) {

                $existingImages = $series->media()->get();
                foreach ($existingImages as $image) {
                    $this->storageService->delete($image);
                    $image->delete();
                }

                // Add new image
                if (!is_null($media)) {
                    foreach ($media as $file) {
                        $newImage = $this->storageService->store($file);
                        $series->media()->save($newImage);
                    }
                }
                $series->save();
            }
            return true;
        });
    }
    
    public function sendRegistrations(int $id): bool
    {
        $series = $this->find($id);

        $seriesTeams = $series->team()->get();

        return DB::transaction(function() use($series, $seriesTeams) {
            foreach ($seriesTeams as $team) {

                $payload = [];
                $payload['series'] = $series->id;
                $payload['team'] = $team->id;
                $encryptedToken = encrypt($payload);
        
                $link = url('/register?id=' . $series->id . '&series=' . urlencode($series->name) . '&price=' . $series->price . '&token=' . $encryptedToken);
                $coach = $team->coach_email;

                if ($coach) {
                    $this->mailService->sendCoachSeriesNotification(
                        coachEmail: $team->coach_email,
                        seriesName: $series->name,
                        link: $link
                    );
                }
            }
            return true;
        });
    }
}
