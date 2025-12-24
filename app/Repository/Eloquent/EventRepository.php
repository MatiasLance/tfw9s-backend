<?php

namespace App\Repository\Eloquent;

use App\Models\Event;
use App\Models\EventMatch;
use App\Models\TeamPosition;
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
        $events = $this->model->query();

        $filters = array_merge($this->defaultEventListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));


        if (!is_null($filters['q'])) {
            $events = $events->where(function ($query) use ($filters) {
                $query->whereHas('eventmatch', function ($q) use ($filters) {
                    $q->whereHas('team1', function ($query) use ($filters) {
                        $query->where('name', 'LIKE', '%' . $filters['q'] . '%');
                    })
                    ->orWhereHas('team2', function ($query) use ($filters) {
                        $query->where('name', 'LIKE', '%' . $filters['q'] . '%');
                    });
                })
                ->orWhereHas('series', function ($query) use ($filters) {
                    $query->where('name', 'LIKE', '%' . $filters['q'] . '%');
                })
                ->orWhereHas('agegroup', function ($query) use ($filters) {
                    $query->where('name', 'LIKE', '%' . $filters['q'] . '%');
                });
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
            case Filter::SORT_LATEST:
                $events = $events->orderBy('event_date', 'desc');
                break;

            default:
                $events = $events->orderBy('created_at', 'desc');
                break;
        }


        $maxPerPage = $filters['max_event_per_page'] ?: 10;

        return new Paginate($events, $maxPerPage, $filters['page'], 'events');
    }

    public function retrieveEvent(int $id): Event
    {
        return $this->find($id);
    }

    public function createEvent(string $time, string $round, int $region_id, int $agegroup_id, int  $series_id, DateTime $datetime, ?array $matches): Event
    {
        $event = new Event();
        $event->time = $time;
        $event->round = $round;
        $event->event_date = $datetime;
        $event->region_id = $region_id;
        $event->agegroup_id = $agegroup_id;
        $event->series_id = $series_id;

        return DB::transaction(function() use ($event, $matches) {
            $event->save();

            if (!empty($matches)) {

                foreach ($matches as $matchesData) {
                    $match = new EventMatch();
                    $match->event_id = $event->id;
                    $match->field_id = $matchesData['field_id'];
                    $match->team1 = $matchesData['team1'];
                    $match->team2 = $matchesData['team2'];
                    $event->eventmatch()->save($match);
                }
            }

            $matchesData = EventMatch::where('event_id', $event->id)->get(['team1', 'team2']);
            $teamPositions = [];

            foreach ($matchesData as $match) {
                foreach (['team1', 'team2'] as $teamKey) {
                    $teamId = $match->$teamKey;

                    if (!isset($teamPositions[$teamId])) {
                        $teamPositions[$teamId] = TeamPosition::where('event_id', $event->id)
                                                            ->where('team_id', $teamId)
                                                            ->value('position');

                        if ($teamPositions[$teamId] === null) {
                            $teamPositions[$teamId] = count($teamPositions);
                        }
                    }

                    $existingPosition = TeamPosition::where('event_id', $event->id)
                                ->where('team_id', $teamId)
                                ->exists();

                    if (!$existingPosition) {
                        $teamPosition = new TeamPosition();
                        $teamPosition->event_id = $event->id;
                        $teamPosition->team_id = $teamId;
                        $teamPosition->position = $teamPositions[$teamId];
                        $teamPosition->save();
                    }
                }
            }
            
            return $event;
        });
    }


    public function updateEvent(int $id, string $time, string $round, int $region_id, int $agegroup_id, DateTime $datetime, ?array $matches): bool
    {
        $event = $this->find($id);
        $event->time = $time;
        $event->round = $round;
        $event->event_date = $datetime;
        $event->region_id = $region_id;
        $event->agegroup_id = $agegroup_id;

        return DB::transaction(function() use($event, $matches, $id) {

            //return $event->save();

            $existingSponsorIds = $event->eventmatch->pluck('id')->toArray();
            $matchesToKeepIds = array_column($matches, 'id');
            $matchesToDeleteIds = array_diff($existingSponsorIds, $matchesToKeepIds);

            foreach ($matches as $index => $matchesData) {

                $existingSponsor = EventMatch::find($matchesData['id']);

                if ($existingSponsor) {
                    $existingSponsor->update([
                        'field_id' => $matchesData['field_id'],
                        'team1' => $matchesData['team1'],
                        'team2' => $matchesData['team2'],
                    ]);

                } else {
                $match = new EventMatch();
                $match->event_id = $event->id;
                $match->field_id = $matchesData['field_id'];
                $match->team1 = $matchesData['team1'];
                $match->team2 = $matchesData['team2'];

                $event->eventmatch()->save($match);
                }
            }
            if (!empty($matchesToDeleteIds)) {
                EventMatch::whereIn('id', $matchesToDeleteIds)->delete();
            }

            $matchesData = EventMatch::where('event_id', $event->id)->get(['team1', 'team2']);
            $teamPositions = [];

            foreach ($matchesData as $match) {
                foreach (['team1', 'team2'] as $teamKey) {
                    $teamId = $match->$teamKey;

                    $existingTeamPosition = TeamPosition::where('event_id', $event->id)
                                                        ->where('team_id', $teamId)
                                                        ->first();

                    if (!$existingTeamPosition) {
                        $teamPosition = new TeamPosition();
                        $teamPosition->event_id = $event->id;
                        $teamPosition->team_id = $teamId;
                        
                        if (!isset($teamPositions[$teamId])) {
                            $teamPositions[$teamId] = count($teamPositions);
                        }
                        $teamPosition->position = $teamPositions[$teamId];
                        
                        $teamPosition->save();
                    }
                }
            }

        return $event->save();
        });
    }

    public function deleteEvent(int $id): bool
    {
        $event = $this->find($id);

        return DB::transaction(function() use($event) {
            TeamPosition::where('event_id', $event->id)->delete();
            EventMatch::where('event_id', $event->id)->delete();
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
