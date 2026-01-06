<?php

namespace App\Repository\Eloquent;

use App\Models\EventMatch;
use App\Models\TeamPosition;
use App\Modules\EventMatch\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Modules\TeamPosition\TeamPositionServiceInterface;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\EventMatchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventMatchRepository extends BaseRepository implements EventMatchRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * TeamPosition Module
     *
     * @var TeamPosition $teamPositionService
     */
    protected TeamPositionServiceInterface $teamPositionService;


    /**
     * Default filters for retrieving list of eventMatchs
     *
     * @var array $defaultEventMatchListFilters
     */
    protected array $defaultEventMatchListFilters = [
        /**
         * Search keyword
         * This filters the eventMatchs with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the eventMatchs according to this value. By default, will sort the eventMatchs by their creation date.
         * For the available sort values, check App\Modules\EventMatch\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of eventMatchs to get
         */
        'page' => 1,

        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'year' => null,

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

        /**
         * event keyword
         * This filters the events with a keyword. When this value is null, this filter is skipped.
         */
        'round' => null,

        /**
         * Max eventMatch per page
         *
         * Maximum number of eventMatchs shown per page. When 0 or null is passed, will get every eventMatch
         */
        'max_eventMatch_per_page' => self::MAX_PAGE_EVENTMATCHES,
    ];

    public function __construct(EventMatch $eventMatch, StorageInterface $storageService, TeamPositionServiceInterface $teamPositionService)
    {
        parent::__construct($eventMatch);
        $this->storageService = $storageService;
        $this->teamPositionService = $teamPositionService;
    }

    // public function listEventMatches(array $userFilters = []): Paginate
    // {
    //     $eventMatches = $this->model->query();

    //     $filters = array_merge($this->defaultEventMatchListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

    //     // Search Filter
    //     if (!is_null($filters['q'])) {
    //         $eventMatches = $eventMatches->where(function ($query) use ($filters) {
    //             $query->whereHas('team1', function ($q) use ($filters) {
    //                 $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
    //             })->orWhereHas('team2', function ($q) use ($filters) {
    //                 $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
    //             });
    //         });
    //     }

    //     switch ($filters['sort']) {
    //     case Filter::SORT_A_TO_Z:
    //         $eventMatches = $eventMatches
    //             ->join('teams as team1', 'event_matches.team1', '=', 'team1.id')
    //             ->join('teams as team2', 'event_matches.team2', '=', 'team2.id')
    //             ->select('event_matches.*')
    //             ->orderBy('team1.name')
    //             ->orderBy('team2.name');
    //         break;

    //     case Filter::SORT_Z_TO_A:
    //         $eventMatches = $eventMatches
    //             ->join('teams as team1', 'event_matches.team1', '=', 'team1.id')
    //             ->join('teams as team2', 'event_matches.team2', '=', 'team2.id')
    //             ->select('event_matches.*')
    //             ->orderByDesc('team1.name')
    //             ->orderByDesc('team2.name');
    //         break;

    //     default:
    //         $eventMatches = $eventMatches->orderBy('created_at');
    //         break;
    //     }

    //     return new Paginate($eventMatches, $filters['max_eventMatch_per_page'], $filters['page'], 'eventMatches');
    // }

    public function listEventMatches(array $userFilters = []): Paginate
    {
        $filters = array_merge(
            $this->defaultEventMatchListFilters,
            array_filter($userFilters, fn ($f) => $f !== null)
        );

        $query = $this->model->query()
            ->select('event_matches.*')
            ->join('events', 'event_matches.event_id', '=', 'events.id')
            ->whereNull('events.deleted_at');

        if (!empty($filters['year'])) {
            $query->whereYear('events.event_date', $filters['year']);
        }

        if (!empty($filters['region'])) {
            $query->where('events.region_id', $filters['region']);
        }

        if (!empty($filters['agegroup'])) {
            $query->where('events.agegroup_id', $filters['agegroup']);
        }

        if (!empty($filters['round'])) {
            $query->where('events.round', $filters['round']);
        }

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('team1', function ($t) use ($filters) {
                    $t->where('name', 'like', "%{$filters['q']}%");
                })->orWhereHas('team2', function ($t) use ($filters) {
                    $t->where('name', 'like', "%{$filters['q']}%");
                });
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
                    ->orderBy('events.time');
                break;
        }

        $query->with([
            'event:id,event_date,region_id,agegroup_id,time,round',
            'team1:id,name',
            'team2:id,name',
            'field:id,name',
        ]);

        return new Paginate(
            $query,
            $filters['max_eventMatch_per_page'],
            $filters['page'],
            'eventMatches'
        );
    }



    public function retrieveEventMatch(int $id): EventMatch
    {
        return $this->find($id);
    }

    public function createEventMatch(int $event_id, int $field_id, int $team1, int $team2): EventMatch
    {
        $eventMatch = new EventMatch();
        $eventMatch->event_id = $event_id;
        $eventMatch->field_id = $field_id;
        $eventMatch->team1 = $team1;
        $eventMatch->team2 = $team2;

        $this->teamPositionService->createTeamPosition($event_id, $team1);
        $this->teamPositionService->createTeamPosition($event_id, $team2);

        return DB::transaction(function() use($eventMatch) {

            return $eventMatch;
        });
    }

    public function updateEventMatch(int $id, int $event_id, int $field_id, int $team1, int $team2): bool
    {
        $eventMatch = $this->find($id);
        $eventMatch->event_id = $event_id;
        $eventMatch->field_id = $field_id;
        $eventMatch->team1 = $team1;
        $eventMatch->team2 = $team2;

        return DB::transaction(function() use($eventMatch) {

            return $eventMatch->save();
        });
    }

    public function updateEventMatchScore(int $id, int $team1_score, int $team2_score): bool
    {
        $eventMatch = $this->find($id);
        $event_id = $eventMatch->event_id;

        $eventMatch->team1_oldScore = $eventMatch->team1_score;
        $eventMatch->team2_oldScore = $eventMatch->team2_score;

        $eventMatch->team1_score = $team1_score;
        $eventMatch->team2_score = $team2_score;

        if ($team1_score > $team2_score) {
            $eventMatch->winner = $eventMatch->team1;
            $eventMatch->losser = $eventMatch->team2;
        } else if ($team2_score > $team1_score) {
            $eventMatch->winner = $eventMatch->team2;
            $eventMatch->losser = $eventMatch->team1;
        } else {
            $eventMatch->winner = null;
            $eventMatch->losser = null;
        }
        $eventMatch->isDraw = ($team1_score == $team2_score);

        return DB::transaction(function() use($eventMatch, $team1_score, $team2_score, $event_id) {
            $eventMatch->save();

            if ($eventMatch->submitted) {
                
                $t1OldScore = $eventMatch->team1_oldScore;
                $t2OldScore = $eventMatch->team2_oldScore;
                $t1score = $eventMatch->team1_score;
                $t2score = $eventMatch->team2_score;
                $updatedFor1 = $t1score - $t1OldScore;
                $updatedFor2 = $t2score - $t2OldScore;

                $addWinCount1 = 'win + ' . 0;
                $addWinCount2 = 'win + ' . 0;
                $addLoseCount1 = 'loss + ' . 0;
                $addLoseCount2 = 'loss + ' . 0;
                $addDrawCount1 = 'draw + ' . 0;
                $addDrawCount2 = 'draw + ' . 0;

                if ($team1_score > $team2_score) {
                    if ($t1OldScore > $t2OldScore) {
                        $addWinCount1 = 'win + ' . 0;
                        $addWinCount2 = 'win + ' . 0;
                        $addLoseCount1 = 'loss + ' . 0;
                        $addLoseCount2 = 'loss + ' . 0;
                        $addDrawCount1 = 'draw + ' . 0;
                        $addDrawCount2 = 'draw + ' . 0;
                    } elseif ($t1OldScore < $t2OldScore) {
                        $addWinCount1 = 'win + ' . 1;
                        $addWinCount2 = 'win - ' . 1;
                        $addLoseCount1 = 'loss - ' . 1;
                        $addLoseCount2 = 'loss + ' . 1;
                        $addDrawCount1 = 'draw + ' . 0;
                        $addDrawCount2 = 'draw + ' . 0;
                    } else {
                        $addWinCount1 = 'win + ' . 1;
                        $addWinCount2 = 'win + ' . 0;
                        $addLoseCount1 = 'loss + ' . 0;
                        $addLoseCount2 = 'loss + ' . 1;
                        $addDrawCount1 = 'draw - ' . 1;
                        $addDrawCount2 = 'draw - ' . 1;
                    }

                } elseif ($team1_score < $team2_score) {
                     if ($t1OldScore > $t2OldScore) {
                        $addWinCount1 = 'win - ' . 1;
                        $addWinCount2 = 'win + ' . 1;
                        $addLoseCount1 = 'loss + ' . 1;
                        $addLoseCount2 = 'loss - ' . 1;
                        $addDrawCount1 = 'draw + ' . 0;
                        $addDrawCount2 = 'draw + ' . 0;
                    } elseif ($t1OldScore < $t2OldScore) {
                        $addWinCount1 = 'win + ' . 0;
                        $addWinCount2 = 'win + ' . 0;
                        $addLoseCount1 = 'loss + ' . 0;
                        $addLoseCount2 = 'loss + ' . 0;
                        $addDrawCount1 = 'draw + ' . 0;
                        $addDrawCount2 = 'draw + ' . 0;
                    } else {
                        $addWinCount1 = 'win + ' . 0;
                        $addWinCount2 = 'win + ' . 1;
                        $addLoseCount1 = 'loss + ' . 1;
                        $addLoseCount2 = 'loss + ' . 0;
                        $addDrawCount1 = 'draw - ' . 1;
                        $addDrawCount2 = 'draw - ' . 1;
                    }
                } else {
                    if ($t1OldScore > $t2OldScore) {
                        $addWinCount1 = 'win - ' . 1;
                        $addWinCount2 = 'win + ' . 0;
                        $addLoseCount1 = 'loss + ' . 0;
                        $addLoseCount2 = 'loss - ' . 1;
                        $addDrawCount1 = 'draw + ' . 1;
                        $addDrawCount2 = 'draw + ' . 1;
                    } elseif ($t1OldScore < $t2OldScore) {
                        $addWinCount1 = 'win + ' . 0;
                        $addWinCount2 = 'win - ' . 1;
                        $addLoseCount1 = 'loss - ' . 1;
                        $addLoseCount2 = 'loss + ' . 0;
                        $addDrawCount1 = 'draw + ' . 1;
                        $addDrawCount2 = 'draw + ' . 1;
                    } else {
                        $addWinCount1 = 'win + ' . 0;
                        $addWinCount2 = 'win + ' . 0;
                        $addLoseCount1 = 'loss + ' . 0;
                        $addLoseCount2 = 'loss + ' . 0;
                        $addDrawCount1 = 'draw + ' . 0;
                        $addDrawCount2 = 'draw + ' . 0;
                    }
                }

                TeamPosition::where('event_id', $event_id)
                    ->where('team_id', $eventMatch->team1)
                    ->update([
                        'win' => DB::raw($addWinCount1),
                        'loss' => DB::raw($addLoseCount1),
                        'draw' => DB::raw($addDrawCount1),
                        'for' => DB::raw('`for` + ' . $updatedFor1),
                        'against' => DB::raw('`against` + ' . $updatedFor2),
                        'difference' => DB::raw('(`for` + ' . $updatedFor1 . ') - (`against` + ' . $updatedFor2 . ')'),
                        'points' => DB::raw('(win * 2) + (draw * 1)'),
                    ]);

                TeamPosition::where('event_id', $event_id)
                    ->where('team_id', $eventMatch->team2)
                    ->update([
                        'win' => DB::raw($addWinCount2),
                        'loss' => DB::raw($addLoseCount2),
                        'draw' => DB::raw($addDrawCount2),
                        'for' => DB::raw('`for` + ' . $updatedFor2),
                        'against' => DB::raw('`against` + ' . $updatedFor1),
                        'difference' => DB::raw('(`for` + ' . $updatedFor2 . ') - (`against` + ' . $updatedFor1 . ')'),
                        'points' => DB::raw('(win * 2) + (draw * 1)'),
                    ]);
            }
             return true;
        });
    }

    public function storeResult(int $id, int $team1_score, int $team2_score): bool
    {
        $eventMatch = $this->find($id);
        $event_id = $eventMatch->event_id;

        if($eventMatch->submitted){
            return false;
        }

        $eventMatch->team1_oldScore = $eventMatch->team1_score;
        $eventMatch->team2_oldScore = $eventMatch->team2_score;

        $eventMatch->team1_score = $team1_score;
        $eventMatch->team2_score = $team2_score;

        if ($team1_score > $team2_score) {
            $eventMatch->winner = $eventMatch->team1;
            $eventMatch->losser = $eventMatch->team2;
        } else if ($team2_score > $team1_score) {
            $eventMatch->winner = $eventMatch->team2;
            $eventMatch->losser = $eventMatch->team1;
        } else {
            $eventMatch->winner = null;
            $eventMatch->losser = null;
        }
        $eventMatch->isDraw = ($team1_score == $team2_score);
        $eventMatch->submitted = true;

        return DB::transaction(function() use($eventMatch, $team1_score, $team2_score, $event_id) {
            $eventMatch->save();

            if ($eventMatch->submitted) {

                if ($team1_score > $team2_score) {
                    $win1 = 1;
                    $loss1 = 0;
                    $draw1 = 0;
                    $win2 = 0;
                    $loss2 = 1;
                    $draw2 = 0;
                    $pts1 = 2;
                    $pts2 = 0;
                } elseif ($team2_score > $team1_score) {
                    $win1 = 0;
                    $loss1 = 1;
                    $draw1 = 0;
                    $win2 = 1;
                    $loss2 = 0;
                    $draw2 = 0;
                    $pts1 = 0;
                    $pts2 = 2;
                } else {
                    $win1 = 0;
                    $loss1 = 0;
                    $draw1 = 1;
                    $win2 = 0;
                    $loss2 = 0;
                    $draw2 = 1;
                    $pts1 = 1;
                    $pts2 = 1;
                }

                $for1 = $team1_score;
                $against1 = $team2_score;
                $diff1 = $team1_score - $team2_score;

                $for2 = $team2_score;
                $against2 = $team1_score;
                $diff2 = $team2_score - $team1_score;

                TeamPosition::where('event_id', $event_id)
                    ->where('team_id', $eventMatch->team1)
                    ->update([
                        'win' => DB::raw('win + ' . $win1),
                        'loss' => DB::raw('loss + ' . $loss1),
                        'draw' => DB::raw('draw + ' . $draw1),
                        'for' => DB::raw('`for` + ' . $for1),
                        'against' => DB::raw('against + ' . $against1),
                        'difference' => DB::raw('difference + ' . $diff1),
                        'points' => DB::raw('points + ' . $pts1),
                    ]);


                TeamPosition::where('event_id', $event_id)
                    ->where('team_id', $eventMatch->team2)
                    ->update([
                        'win' => DB::raw('win + ' . $win2),
                        'loss' => DB::raw('loss + ' . $loss2),
                        'draw' => DB::raw('draw + ' . $draw2),
                        'for' => DB::raw('`for` + ' . $for2),
                        'against' => DB::raw('against + ' . $against2),
                        'difference' => DB::raw('difference + ' . $diff2),
                        'points' => DB::raw('points + ' . $pts2),
                    ]);
            }
             return true;
        });
    }

    public function deleteEventMatch(int $id): bool
    {
        $eventMatch = $this->find($id);

        return DB::transaction(function() use($eventMatch) {

            return $eventMatch->delete();
        });
    }

    public function addVideo(int $id, UploadedFile $video): bool
    {
        $eventMatch = $this->find($id);

        return DB::transaction(function() use($eventMatch, $video) {

            $fileType = $this->storageService->determineFileType($video);

            $matchVideo = $this->storageService->storeVideo($video, $eventMatch, $fileType);
            $eventMatch->video()->save($matchVideo);

            return true;
        });
    }

    private function decision(int $team1, int $team2, int $team1_score, int $team2_score): array
    {
        $winner = null;
        $losser = null; $isDraw = false;
        if ($team1_score > $team2_score) {
            $winner = $team1;
            $losser = $team2;
        } elseif ($team1_score < $team2_score) {
            $winner = $team2;
            $losser = $team1;
        } else {
            $isDraw = true;
        }

        return [$winner, $losser, $isDraw];

    }
}
