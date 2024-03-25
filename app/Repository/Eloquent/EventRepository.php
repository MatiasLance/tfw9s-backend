<?php

namespace App\Repository\Eloquent;

use App\Models\Event;
use App\Modules\Event\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use DateTime;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of events
     *
     * @var array $defaultEventListFilters
     */
    protected array $defaultEventListFilters = [
        /**
         * Search keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the events according to this value. By default, will sort the events by their creation date.
         * For the available sort values, check App\Modules\Event\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of events to get
         */
        'page' => 1,

        /**
         * Max event per page
         *
         * Maximum number of events shown per page. When 0 or null is passed, will get every event
         */
        'max_event_per_page' => self::MAX_PAGE_EVENTS,
    ];

    public function __construct(Event $event, StorageInterface $storageService)
    {
        parent::__construct($event);
        $this->storageService = $storageService;
    }

    public function listEvents(array $userFilters = []): Paginate
    {
        $events = $this->model->query();

        $filters = array_merge($this->defaultEventListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $events = $events->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $events = $events->orderByDesc('name');
                break;

            default:
                $events = $events->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_event_per_page']) ? $events->count() : $filters['max_region_per_page'];

        return new Paginate($events, $maxPerPage, $filters['page'], 'events');
    }

    public function retrieveEvent(int $id): Event
    {
        return $this->find($id);
    }

    public function createEvent(string $name, string $description, DateTime $datetime, int $field_id): Event
    {
        $event = new Event();
        $event->name = $name;
        $event->description = $description;
        $event->datetime = $datetime;
        $event->field_id = $field_id;

        return DB::transaction(function() use($event) {
            $event->save();

            return $event;
        });
    }

    public function updateEvent(int $id, string $name, string $description, DateTime $datetime, int $field_id): bool
    {
        $event = $this->find($id);
        $event->name = $name;
        $event->description = $description;
        $event->datetime = $datetime;
        $event->field_id = $field_id;

        return DB::transaction(function() use($event) {

            return $event->save();
        });
    }

    public function deleteEvent(int $id): bool
    {
        $event = $this->find($id);

        return DB::transaction(function() use($event) {

            return $event->delete();
        });
    }
}
