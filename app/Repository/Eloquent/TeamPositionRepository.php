<?php

namespace App\Repository\Eloquent;

use App\Models\TeamPosition;
use App\Models\EventMatch;
use App\Modules\TeamPosition\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TeamPositionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TeamPositionRepository extends BaseRepository implements TeamPositionRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of teamPositions
     *
     * @var array $defaultTeamPositionListFilters
     */
    protected array $defaultTeamPositionListFilters = [
        /**
         * Search keyword
         * This filters the teamPositions with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the teamPositions according to this value. By default, will sort the teamPositions by their creation date.
         * For the available sort values, check App\Modules\TeamPosition\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of teamPositions to get
         */
        'page' => 1,

        /**
         * Max teamPosition per page
         *
         * Maximum number of teamPositions shown per page. When 0 or null is passed, will get every teamPosition
         */
        'max_teamPosition_per_page' => self::MAX_PAGE_TEAMPOSITIONS,
    ];

    public function __construct(TeamPosition $teamPosition, StorageInterface $storageService)
    {
        parent::__construct($teamPosition);
        $this->storageService = $storageService;
    }

    public function listTeamPositions(array $userFilters = []): Paginate
    {
        $teamPositions = $this->model->query();

        $filters = array_merge($this->defaultTeamPositionListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $teamPositions = $teamPositions->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $teamPositions = $teamPositions->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $teamPositions = $teamPositions->orderByDesc('name');
                break;

            default:
                $teamPositions = $teamPositions->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_teamPosition_per_page']) ? $teamPositions->count() : $filters['max_field_per_page'];

        return new Paginate($teamPositions, $maxPerPage, $filters['page'], 'teamPositions');
    }

    public function retrieveTeamPosition(int $id): TeamPosition
    {
        return $this->find($id);
    }

    public function createTeamPosition(int $event_id, int $team_id): TeamPosition
    {
        $defaultPosition = TeamPosition::where('event_id', $event_id)->get()->toArray();

        $teamPosition = new TeamPosition();
        $teamPosition->event_id = $event_id;
        $teamPosition->team_id = $team_id;

        $teamsCount = count($defaultPosition);

        $teamPosition->position = $teamsCount += 1;

        return DB::transaction(function() use($teamPosition, $event_id) {
            $teamPosition->save();

            $this->updatePosition($event_id);

            return $teamPosition;
        });
    }

    public function updateTeamPosition(int $event_id, int $eventMatch_id): bool
    {
        $eventMatch = EventMatch::where('id', $eventMatch_id)->firstOrFail();
        $team1 = $eventMatch->team1;
        $team2 = $eventMatch->team2;

        $team1Position = TeamPosition::where('team_id', $team1)
            ->where('event_id', $event_id)
            ->firstOrFail();
        $team2Position = TeamPosition::where('team_id', $team2)
            ->where('event_id', $event_id)
            ->firstOrFail();

        $team1Position->for += $eventMatch->team1_score;
        $team1Position->against += $eventMatch->team2_score;

        $team2Position->for += $eventMatch->team2_score;
        $team2Position->against += $eventMatch->team1_score;

        $team1Position->difference = $team1Position->for - $team1Position->against;
        $team2Position->difference = $team2Position->for - $team2Position->against;

        if ($eventMatch->winner == $team1) {
            $team1Position->win += 1;
            $team1Position->points += 4;

            $team2Position->loss += 1;
        } elseif ($eventMatch->winner == $team2) {
            $team2Position->win += 1;
            $team2Position->points += 4;

            $team1Position->loss += 1;
        } else {
            $team1Position->draw += 1;
            $team1Position->points += 2;

            $team2Position->draw += 1;
            $team2Position->points += 2;
        }

        TeamPosition::where('event_id', $event_id)->orderByDesc('points')->get()->each(function ($position, $index) {
            $position->position = $index + 1;
            $position->save();
        });

        return true;
    }

    public function deleteTeamPosition(int $id): bool
    {
        $teamPosition = $this->find($id);
        $event_id = $teamPosition->event_id;

        return DB::transaction(function() use($teamPosition, $event_id) {
            $teamPosition->delete();

            $this->updatePosition($event_id);

            return true;
        });
    }

    private function updatePosition(int $event_id)
    {
        $positions = TeamPosition::where('event_id', $event_id)->orderBy('created_at')->get();
        $position = 1;

        foreach ($positions as $pos) {
            $pos->position = $position++;
            $pos->save();
        }
    }
}
