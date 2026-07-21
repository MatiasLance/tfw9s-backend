<?php

namespace App\Repository\Eloquent;

use App\Models\EventMatch;
use App\Models\TeamPosition;
use App\Modules\EventMatch\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\TeamPosition\TeamPositionServiceInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\EventMatchRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class EventMatchRepository extends BaseRepository implements EventMatchRepositoryInterface
{
    protected StorageInterface $storageService;

    protected TeamPositionServiceInterface $teamPositionService;

    protected array $defaultEventMatchListFilters = [
        'q' => null,
        'sort' => Filter::SORT_LATEST,
        'page' => 1,
        'year' => null,
        'region' => null,
        'agegroup' => null,
        'round' => null,
        'series' => null,
        'series_id' => null,
        'event_date' => null,
        'status' => null,
        'max_eventMatch_per_page' => self::MAX_PAGE_EVENTMATCHES,
    ];

    public function __construct(
        EventMatch $eventMatch,
        StorageInterface $storageService,
        TeamPositionServiceInterface $teamPositionService
    ) {
        parent::__construct($eventMatch);
        $this->storageService = $storageService;
        $this->teamPositionService = $teamPositionService;
    }

    public function listEventMatches(array $userFilters = []): Paginate
    {
        $filters = array_merge(
            $this->defaultEventMatchListFilters,
            array_filter($userFilters, fn ($filter) => $filter !== null)
        );

        $query = $this->model->query()
            ->select('event_matches.*')
            ->join('events', 'event_matches.event_id', '=', 'events.id')
            ->join('series', 'events.series_id', '=', 'series.id')
            ->whereNull('events.deleted_at')
            ->whereNull('series.deleted_at');

        if ($filters['year']) {
            $query->whereYear('events.event_date', $filters['year']);
        }

        if ($filters['event_date']) {
            $query->whereDate('events.event_date', $filters['event_date']);
        }

        if ($filters['region']) {
            $query->where('events.region_id', $filters['region']);
        }

        if ($filters['agegroup']) {
            $query->where('events.agegroup_id', $filters['agegroup']);
        }

        if ($filters['round']) {
            $query->where('events.round', $filters['round']);
        }

        if ($filters['series_id']) {
            $query->where('events.series_id', $filters['series_id']);
        } elseif ($filters['series']) {
            $query->where('series.name', $filters['series']);
        }

        if ($filters['status'] === 'complete') {
            $query->where('event_matches.submitted', true);
        } elseif ($filters['status'] === 'upcoming') {
            $query->where('event_matches.submitted', false);
        }

        if ($filters['q']) {
            $search = addcslashes($filters['q'], '%_\\');
            $query->where(function ($matchQuery) use ($search) {
                $matchQuery
                    ->whereHas('team1', fn ($teamQuery) => $teamQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('team2', fn ($teamQuery) => $teamQuery->where('name', 'like', "%{$search}%"));
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $query
                    ->leftJoin('teams as t1', 'event_matches.team1', '=', 't1.id')
                    ->leftJoin('teams as t2', 'event_matches.team2', '=', 't2.id')
                    ->orderBy('t1.name')
                    ->orderBy('t2.name');
                break;

            case Filter::SORT_Z_TO_A:
                $query
                    ->leftJoin('teams as t1', 'event_matches.team1', '=', 't1.id')
                    ->leftJoin('teams as t2', 'event_matches.team2', '=', 't2.id')
                    ->orderByDesc('t1.name')
                    ->orderByDesc('t2.name');
                break;

            default:
                $query
                    ->orderBy('events.event_date')
                    ->orderBy('events.time')
                    ->orderBy('event_matches.id');
        }

        $teamRelations = ['field', 'agegroup', 'series', 'region'];
        $query->with([
            'event' => fn ($eventQuery) => $eventQuery
                ->select('id', 'event_date', 'region_id', 'agegroup_id', 'time', 'round', 'series_id')
                ->without(['manager', 'eventmatch'])
                ->with(['series:id,name', 'agegroup:id,name', 'region:id,name']),
            'team1' => fn ($teamQuery) => $teamQuery
                ->select('id', 'name')
                ->without($teamRelations)
                ->with('media'),
            'team2' => fn ($teamQuery) => $teamQuery
                ->select('id', 'name')
                ->without($teamRelations)
                ->with('media'),
            'field:id,name',
        ]);

        return (new Paginate(
            $query,
            $filters['max_eventMatch_per_page'],
            $filters['page'],
            'eventMatches'
        ))->transformItems(function (EventMatch $match) {
            foreach (['team1', 'team2'] as $relation) {
                if ($match->relationLoaded($relation) && $match->getRelation($relation)) {
                    $match->getRelation($relation)->setAppends([]);
                }
            }

            return $match;
        });
    }

    public function retrieveEventMatch(int $id): EventMatch
    {
        return $this->find($id);
    }

    public function createEventMatch(int $event_id, int $field_id, int $team1, int $team2): EventMatch
    {
        return DB::transaction(function () use ($event_id, $field_id, $team1, $team2) {
            $eventMatch = new EventMatch();
            $eventMatch->event_id = $event_id;
            $eventMatch->field_id = $field_id;
            $eventMatch->team1 = $team1;
            $eventMatch->team2 = $team2;
            $eventMatch->save();

            $this->teamPositionService->createTeamPosition($event_id, $team1);
            $this->teamPositionService->createTeamPosition($event_id, $team2);

            return $eventMatch;
        });
    }

    public function updateEventMatch(int $id, int $event_id, int $field_id, int $team1, int $team2): bool
    {
        return DB::transaction(function () use ($id, $event_id, $field_id, $team1, $team2) {
            [$eventMatch] = $this->lockEventMatchGroup($id);
            $previousEventId = $eventMatch->event_id;

            $eventMatch->event_id = $event_id;
            $eventMatch->field_id = $field_id;
            $eventMatch->team1 = $team1;
            $eventMatch->team2 = $team2;
            $saved = $eventMatch->save();

            if ($eventMatch->submitted) {
                $this->rebuildStandings($previousEventId);
                if ($previousEventId !== $event_id) {
                    $this->rebuildStandings($event_id);
                }
            }

            return $saved;
        });
    }

    public function updateEventMatchScore(
        int $id,
        int $team1_score,
        int $team2_score,
        bool $isAbandonedMatch
    ): bool {
        return DB::transaction(function () use ($id, $team1_score, $team2_score, $isAbandonedMatch) {
            [$eventMatch] = $this->lockEventMatchGroup($id);

            $eventMatch->team1_oldScore = $eventMatch->team1_score;
            $eventMatch->team2_oldScore = $eventMatch->team2_score;
            $eventMatch->team1_score = $team1_score;
            $eventMatch->team2_score = $team2_score;
            $eventMatch->is_abandoned_match = $isAbandonedMatch;
            [$eventMatch->winner, $eventMatch->losser, $eventMatch->isDraw] = $isAbandonedMatch
                ? [null, null, true]
                : $this->decision($eventMatch->team1, $eventMatch->team2, $team1_score, $team2_score);
            $eventMatch->save();

            if ($eventMatch->submitted) {
                $this->rebuildStandings($eventMatch->event_id);
            }

            return true;
        });
    }

    public function storeResult(int $id, int $team1_score, int $team2_score): bool
    {
        return DB::transaction(function () use ($id, $team1_score, $team2_score) {
            [$eventMatch] = $this->lockEventMatchGroup($id);

            if ($eventMatch->submitted) {
                return false;
            }

            $eventMatch->team1_oldScore = $eventMatch->team1_score;
            $eventMatch->team2_oldScore = $eventMatch->team2_score;
            $eventMatch->team1_score = $team1_score;
            $eventMatch->team2_score = $team2_score;
            [$eventMatch->winner, $eventMatch->losser, $eventMatch->isDraw] = $this->decision(
                $eventMatch->team1,
                $eventMatch->team2,
                $team1_score,
                $team2_score
            );
            $eventMatch->submitted = true;
            $eventMatch->save();

            $this->rebuildStandings($eventMatch->event_id);

            return true;
        });
    }

    public function revertResult(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            [$eventMatch] = $this->lockEventMatchGroup($id);

            if (! $eventMatch->submitted) {
                return true;
            }

            $eventMatch->submitted = false;
            $eventMatch->winner = null;
            $eventMatch->losser = null;
            $eventMatch->isDraw = false;
            $eventMatch->is_abandoned_match = false;
            $eventMatch->save();

            $this->rebuildStandings($eventMatch->event_id);

            return true;
        });
    }

    public function deleteEventMatch(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            [$eventMatch] = $this->lockEventMatchGroup($id);
            $eventId = $eventMatch->event_id;
            $wasSubmitted = $eventMatch->submitted;
            $deleted = $eventMatch->delete();

            if ($wasSubmitted) {
                $this->rebuildStandings($eventId);
            }

            return $deleted;
        });
    }

    public function addVideo(int $id, UploadedFile $video): bool
    {
        $eventMatch = $this->find($id);

        return DB::transaction(function () use ($eventMatch, $video) {
            $fileType = $this->storageService->determineFileType($video);
            $matchVideo = $this->storageService->storeVideo($video, $eventMatch, $fileType);
            $eventMatch->video()->save($matchVideo);

            return true;
        });
    }

    private function rebuildStandings(int $eventId): void
    {
        $matches = EventMatch::query()
            ->without(['team1', 'team2', 'field'])
            ->where('event_id', $eventId)
            ->where('submitted', true)
            ->lockForUpdate()
            ->get(['team1', 'team2', 'team1_score', 'team2_score', 'is_abandoned_match']);

        $positions = TeamPosition::query()
            ->without(['team', 'event'])
            ->where('event_id', $eventId)
            ->lockForUpdate()
            ->get()
            ->keyBy('team_id');

        $totals = [];
        foreach ($positions->keys() as $teamId) {
            $totals[$teamId] = [
                'win' => 0,
                'loss' => 0,
                'draw' => 0,
                'for' => 0,
                'against' => 0,
                'difference' => 0,
                'points' => 0,
            ];
        }

        foreach ($matches as $match) {
            if (! isset($totals[$match->team1], $totals[$match->team2])) {
                continue;
            }

            $team1Score = (int) $match->team1_score;
            $team2Score = (int) $match->team2_score;
            $totals[$match->team1]['for'] += $team1Score;
            $totals[$match->team1]['against'] += $team2Score;
            $totals[$match->team2]['for'] += $team2Score;
            $totals[$match->team2]['against'] += $team1Score;

            if ($match->is_abandoned_match || $team1Score === $team2Score) {
                $totals[$match->team1]['draw']++;
                $totals[$match->team2]['draw']++;
                $totals[$match->team1]['points']++;
                $totals[$match->team2]['points']++;
            } elseif ($team1Score > $team2Score) {
                $totals[$match->team1]['win']++;
                $totals[$match->team1]['points'] += 2;
                $totals[$match->team2]['loss']++;
            } else {
                $totals[$match->team2]['win']++;
                $totals[$match->team2]['points'] += 2;
                $totals[$match->team1]['loss']++;
            }
        }

        foreach ($positions as $teamId => $position) {
            $totals[$teamId]['difference'] = $totals[$teamId]['for'] - $totals[$teamId]['against'];
            $position->forceFill($totals[$teamId])->save();
        }
    }

    private function lockEventMatchGroup(int $id): array
    {
        $eventId = EventMatch::query()
            ->without(['team1', 'team2', 'field'])
            ->whereKey($id)
            ->value('event_id');

        if (! $eventId) {
            throw (new ModelNotFoundException())->setModel(EventMatch::class, [$id]);
        }

        $matches = EventMatch::query()
            ->without(['team1', 'team2', 'field'])
            ->where('event_id', $eventId)
            ->lockForUpdate()
            ->get();
        $eventMatch = $matches->firstWhere('id', $id);

        if (! $eventMatch) {
            throw (new ModelNotFoundException())->setModel(EventMatch::class, [$id]);
        }

        return [$eventMatch, $matches];
    }

    private function decision(int $team1, int $team2, int $team1_score, int $team2_score): array
    {
        if ($team1_score > $team2_score) {
            return [$team1, $team2, false];
        }

        if ($team2_score > $team1_score) {
            return [$team2, $team1, false];
        }

        return [null, null, true];
    }
}
