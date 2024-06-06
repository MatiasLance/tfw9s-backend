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

    public function listEventMatches(array $userFilters = []): Paginate
    {
        $eventMatches = $this->model->query();

        $filters = array_merge($this->defaultEventMatchListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $eventMatches = $eventMatches->where(function ($query) use ($filters) {
                $query->whereHas('team1', function ($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
                })->orWhereHas('team2', function ($q) use ($filters) {
                    $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
                });
            });
        }

        switch ($filters['sort']) {
        case Filter::SORT_A_TO_Z:
            $eventMatches = $eventMatches
                ->join('teams as team1', 'event_matches.team1', '=', 'team1.id')
                ->join('teams as team2', 'event_matches.team2', '=', 'team2.id')
                ->select('event_matches.*')
                ->orderBy('team1.name')
                ->orderBy('team2.name');
            break;

        case Filter::SORT_Z_TO_A:
            $eventMatches = $eventMatches
                ->join('teams as team1', 'event_matches.team1', '=', 'team1.id')
                ->join('teams as team2', 'event_matches.team2', '=', 'team2.id')
                ->select('event_matches.*')
                ->orderByDesc('team1.name')
                ->orderByDesc('team2.name');
            break;

        default:
            $eventMatches = $eventMatches->orderBy('created_at');
            break;
        }

        $maxPerPage = is_null($userFilters['max_eventMatch_per_page']) ? $eventMatches->count() : $filters['max_eventMatch_per_page'];

        return new Paginate($eventMatches, $maxPerPage, $filters['page'], 'eventMatches');
    }

    public function retrieveEventMatch(int $id): EventMatch
    {
        return $this->find($id);
    }

    public function createEventMatch(int $event_id, int $field_id, string $match_time, int $team1, int $team2): EventMatch
    {
        $eventMatch = new EventMatch();
        $eventMatch->event_id = $event_id;
        $eventMatch->field_id = $field_id;
        $eventMatch->match_time = $match_time;
        $eventMatch->team1 = $team1;
        $eventMatch->team2 = $team2;

        $this->teamPositionService->createTeamPosition($event_id, $team1);
        $this->teamPositionService->createTeamPosition($event_id, $team2);

        return DB::transaction(function() use($eventMatch) {

            return $eventMatch;
        });
    }

    public function updateEventMatch(int $id, int $event_id, int $field_id, string $match_time, int $team1, int $team2): bool
    {
        $eventMatch = $this->find($id);
        $eventMatch->event_id = $event_id;
        $eventMatch->field_id = $field_id;
        $eventMatch->match_time = $match_time;
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
                $win1 = ($team1_score > $team2_score) ? 1 : 0;
                $loss1 = ($team1_score > $team2_score) ? 0 : 1;
                $draw1 = ($team1_score == $team2_score);
                $for1 = $team1_score;
                $against1 = $team2_score;
                $diff1 = $team1_score - $team2_score;
                $pts1 = ($team1_score > $team2_score) ? 2 : 0;
            
                $win2 = ($team2_score > $team1_score) ? 1 : 0;
                $loss2 = ($team2_score > $team1_score) ? 0 : 1;
                $draw2 = ($team1_score == $team2_score);
                $for2 = $team2_score;
                $against2 = $team1_score;
                $diff2 = $team2_score - $team1_score;
                $pts2 = ($team2_score > $team1_score) ? 2 : 0;
            
                TeamPosition::where('event_id', $event_id)
                    ->where('team_id', $eventMatch->team1)
                    ->update([
                        'win' => $win1,
                        'loss' => $loss1,
                        'draw' => $draw1,
                        'for' => $for1,
                        'against' => $against1,
                        'difference' => $diff1,
                        'points' => $pts1,
                    ]);
            
                TeamPosition::where('event_id', $event_id)
                    ->where('team_id', $eventMatch->team2)
                    ->update([
                        'win' => $win2,
                        'loss' => $loss2,
                        'draw' => $draw2,
                        'for' => $for2,
                        'against' => $against2,
                        'difference' => $diff2,
                        'points' => $pts2,
                    ]);
            }
            
             return true;
        });
    }

    public function storeResult(int $id, int $team1_score, int $team2_score): bool
    {
        $eventMatch = $this->find($id);
        $team1 = $eventMatch->team1;
        $team2 = $eventMatch->team2;
        $event_id = $eventMatch->event_id;

        $existingResult = [
            'team1_score' => $eventMatch->team1_oldScore,
            'team2_score' => $eventMatch->team2_oldScore,
            'winner' => $eventMatch->winner,
            'losser' => $eventMatch->losser,
            'isDraw' => $eventMatch->isDraw
        ];

        list($winner, $losser, $isDraw) = $this->decision($team1, $team2, $team1_score, $team2_score);

        $eventMatch->team1_score = $team1_score;
        $eventMatch->team2_score = $team2_score;
        $eventMatch->winner = $winner;
        $eventMatch->losser = $losser;
        $eventMatch->isDraw = $isDraw;
        $eventMatch->submitted = true;

        return DB::transaction(function() use($eventMatch, $event_id, $id, $existingResult) {
            $eventMatch->save();

            $isSuccess = $this->teamPositionService->updateTeamPosition($event_id, $id, $existingResult);

            return $isSuccess;
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
