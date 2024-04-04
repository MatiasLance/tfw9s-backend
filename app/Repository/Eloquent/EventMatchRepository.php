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

        $maxPerPage = is_null($userFilters['max_eventMatch_per_page']) ? $eventMatches->count() : $filters['max_field_per_page'];

        return new Paginate($eventMatches, $maxPerPage, $filters['page'], 'eventMatches');
    }

    public function retrieveEventMatch(int $id): EventMatch
    {
        return $this->find($id);
    }

    public function createEventMatch(int $event_id, string $match_time, int $team1, int $team2): EventMatch
    {
        $eventMatch = new EventMatch();
        $eventMatch->event_id = $event_id;
        $eventMatch->match_time = $match_time;
        $eventMatch->team1 = $team1;
        $eventMatch->team2 = $team2;

        $this->teamPositionService->createTeamPosition($event_id, $team1);
        $this->teamPositionService->createTeamPosition($event_id, $team2);

        return DB::transaction(function() use($eventMatch) {

            return $eventMatch;
        });
    }

    public function updateEventMatch(int $id, int $event_id, string $match_time, int $team1, int $team2): bool
    {
        $eventMatch = $this->find($id);
        $eventMatch->event_id = $event_id;
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
        $eventMatch->team1_oldScore = $eventMatch->team1_score;
        $eventMatch->team2_oldScore = $eventMatch->team2_score;

        $eventMatch->team1_score = $team1_score;
        $eventMatch->team2_score = $team2_score;

        return DB::transaction(function() use($eventMatch) {

            return $eventMatch->save();
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
