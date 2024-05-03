<?php

namespace App\Repository\Eloquent;

use App\Models\Event;
use App\Models\EventMatch;
use App\Modules\Event\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use App\Modules\EventMatch\EventMatchServiceInterface;
use DateTime;

class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    protected EventMatchServiceInterface $eventmatchService;
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
         * Event Date keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'event_date' => null,

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

        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'event' => null,

        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'year' => null,

        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'manager' => null,
        
        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'region' => null,
                
        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'agegroup' => null,
    ];

    public function __construct(Event $event, StorageInterface $storageService, EventMatchServiceInterface $eventmatchService)
    {
        parent::__construct($event);
        $this->eventmatchService = $eventmatchService;
        $this->storageService = $storageService;
    }

    public function listEvents(array $userFilters = []): Paginate
    {
        $events = $this->model->query()->with('region', 'manager', 'agegroup', 'series', 'eventmatch');

        $filters = array_merge($this->defaultEventListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        // Event Date Filter
        if (!is_null($filters['event_date'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('event_date', 'LIKE', '%' . $filters['event_date'] . '%');
            });
        }

        // Year Filter
        if (!is_null($filters['year'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('event_date', 'LIKE', '%' . $filters['year'] . '%');
            });
        }

        // Event Filter
        if (!is_null($filters['event'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('id', 'LIKE', '%' . $filters['event'] . '%');
            });
        }

        // Event Filter
        if (!is_null($filters['manager'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('manager_id', '=', $filters['manager']);
            });
        }

        // Region Filter
        if (!is_null($filters['region'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('region_id', '=', $filters['region']);
            });
        }

        if (!is_null($filters['agegroup'])) {
            $events = $events->where(function ($q) use($filters) {
                $q
                    ->where('agegroup_id', '=', $filters['agegroup']);
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $events = $events->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $events = $events->orderByDesc('name');
                break;

                case Filter::SORT_LATEST:
                    $events = $events->orderBy('event_date');
                    break;

            default:
                $events = $events->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_event_per_page']) ? $events->count() : $filters['max_event_per_page'];

        return new Paginate($events, $maxPerPage, $filters['page'], 'events');
    }

    public function retrieveEvent(int $id): Event
    {
        return Event::with('region', 'manager', 'agegroup', 'series', 'eventmatch')->find($id);
    }

    public function createEvent(string $name, string $description, DateTime $datetime, int $region_id, int $manager_id, int $agegroup_id, int $series, int $teamcount, ?array $matches): Event
    {
        $event = new Event();
        $event->name = $name;
        $event->description = $description;
        $event->event_date = $datetime;
        $event->region_id = $region_id;
        $event->manager_id = $manager_id;
        $event->agegroup_id = $agegroup_id;
        $event->series_id = $series;
        $event->teamcount = $teamcount;

        return DB::transaction(function() use($event, $matches) {
            $event->save();

            foreach ($matches as $index => $matchesData) {
                // $match = new EventMatch();
                // $match->event_id = $event->id;
                // $match->match_time= $matchesData['time'];
                // $match->team1 = $matchesData['team1'];
                // $match->team2 = $matchesData['team2'];

                // $event->eventmatch()->save($match);
                $event_id = $event->id;
                $field_id = $matchesData['field_id'];
                $match_time = $matchesData['time'];
                $team1 = $matchesData['team1'];
                $team2 = $matchesData['team2'];
                $team1_score = 0;
                $team2_score = 0;
                $matches = $this->eventmatchService->createEventMatch($event_id, $field_id, $match_time, $team1, $team2, $team1_score, $team2_score);
                $event->eventmatch()->save($matches);
            }

            return $event;
        });
    }

    public function updateEvent(int $id, string $name, string $description, DateTime $datetime, int $region_id, $manager_id, int $agegroup_id, int $series, int $teamcount, ?array $matches): bool
    {
        $event = $this->find($id);
        $event->name = $name;
        $event->description = $description;
        $event->event_date = $datetime;
        $event->region_id = $region_id;
        $event->manager_id = $manager_id;
        $event->agegroup_id = $agegroup_id;
        $event->series_id = $series;
        $event->teamcount = $teamcount;

        return DB::transaction(function() use($event, $matches) {

            //return $event->save();

            $existingSponsorIds = $event->eventmatch->pluck('id')->toArray();
            $matchesToKeepIds = array_column($matches, 'id');
            $matchesToDeleteIds = array_diff($existingSponsorIds, $matchesToKeepIds);

            foreach ($matches as $index => $matchesData) {

                $existingSponsor = EventMatch::find($matchesData['id']);

                if ($existingSponsor) {
                    $existingSponsor->update([
                        'match_time' => $matchesData['time'],
                        'field_id' => $matchesData['field_id'],
                        'team1' => $matchesData['team1'],
                        'team2' => $matchesData['team2'],
                    ]);

                } else {
                $match = new EventMatch();
                $match->event_id = $event->id;
                $match->field_id = $matchesData['field_id'];
                $match->match_time= $matchesData['time'];
                $match->team1 = $matchesData['team1'];
                $match->team2 = $matchesData['team2'];

                $event->eventmatch()->save($match);
                }
            }

            if (!empty($matchesToDeleteIds)) {
                EventMatch::whereIn('id', $matchesToDeleteIds)->delete();
            }
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

    public function allEvents(array $userFilters = []): Paginate
    {
        $events = $this->model->query()->select('id', 'name', 'agegroup_id', 'event_date')->with('eventmatch')->orderBy('name');

        $filters = array_merge($this->defaultEventListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        $maxPerPage = is_null($userFilters['max_event_per_page']) ? $events->count() : $filters['max_event_per_page'];

        return new Paginate($events, $maxPerPage, $filters['page'], 'events');
    }
}
