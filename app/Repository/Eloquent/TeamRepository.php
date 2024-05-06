<?php

namespace App\Repository\Eloquent;

use App\Models\Team;
use App\Modules\Team\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\TeamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class TeamRepository extends BaseRepository implements teamRepositoryInterface
{
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of teams
     *
     * @var array $defaultTeamListFilters
     */
    protected array $defaultTeamListFilters = [
        /**
         * Search keyword
         * This filters the teams with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the teams according to this value. By default, will sort the teams by their creation date.
         * For the available sort values, check App\Modules\Team\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of teams to get
         */
        'page' => 1,

        /**
         * Max team per page
         *
         * Maximum number of teams shown per page. When 0 or null is passed, will get every team
         */
        'max_team_per_page' => self::MAX_PAGE_TEAMS,

        /**
         * Name keyword
         * When this value is null, this filter is skipped.
         */
        'name' => null,

        /**
         * Mobile keyword
         * When this value is null, this filter is skipped.
         */
        'mobile' => null,

        /**
         * Email keyword
         * When this value is null, this filter is skipped.
         */
        'email' => null,
    ];

    public function __construct(Team $team, StorageInterface $storageService)
    {
        parent::__construct($team);
        $this->storageService = $storageService;
    }

    public function listTeams(array $userFilters = []): Paginate
    {
        $teams = $this->model->query()->with('field', 'agegroup');

        $filters = array_merge($this->defaultTeamListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $teams = $teams->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $teams = $teams->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $teams = $teams->orderByDesc('name');
                break;

            default:
                $teams = $teams->orderBy('created_at');
                break;
        }

        $maxPerPage = is_null($userFilters['max_team_per_page']) ? $teams->count() : $filters['max_team_per_page'];

        return new Paginate($teams, $maxPerPage, $filters['page'], 'teams');
    }

    public function retrieveTeam(int $id): Team
    {
        return Team::with('field', 'agegroup')->find($id);
    }

    public function createTeam(string $name, string $description, int $field_id, int $agegroup_id, array $coach, array $manager, ?array $media): Team
    {
        $team = new Team();
        $team->name = $name;
        $team->description = $description;
        $team->field_id = $field_id;
        $team->agegroup_id = $agegroup_id;
        $team->coach_name = $coach['name'];
        $team->coach_mobile = $coach['mobile'];
        $team->coach_email = $coach['email'];
        $team->manager_name = $manager['name'];
        $team->manager_mobile = $manager['mobile'];
        $team->manager_email = $manager['email'];

        return DB::transaction(function() use($team,$media) {
            $team->save();

            foreach ($media as $file) {
                if (!is_null($file)) {
  
                    $teamImage = $this->storageService->store($file);
                    $team->media()->save($teamImage);
                }
              }

            return $team;
        });
    }

    public function updateTeam(int $id, string $name, string $description, int $field_id, int $agegroup_id, array $coach, array $manager, ?array $media): bool
    {
        $team = $this->find($id);
        $team->name = $name;
        $team->description = $description;
        $team->field_id = $field_id;
        $team->agegroup_id = $agegroup_id;
        $team->coach_name = $coach['name'];
        $team->coach_mobile = $coach['mobile'];
        $team->coach_email = $coach['email'];
        $team->manager_name = $manager['name'];
        $team->manager_mobile = $manager['mobile'];
        $team->manager_email = $manager['email'];

        return DB::transaction(function() use($team, $media) {

            if (!is_null($media)) {
                $newMedia = array_filter($media, function ($file) {
                    return $file instanceof UploadedFile;
                });

                $oldMedia = array_filter($media, function ($file) {
                    return !$file instanceof UploadedFile;
                });

                foreach ($team->media as $existingMedia) {
                    if (
                        $existingMedia->path !== 'media/default/' . self::PLACEHOLDER_IMAGE &&
                        !in_array($existingMedia->hash, $oldMedia)
                    ) {
                        $this->storageService->delete($existingMedia);
                        $existingMedia->delete();
                    }
                }

                foreach ($newMedia as $newFile) {

                    $teamImage = $this->storageService->store($newFile);
                    $team->media()->save($teamImage);
                }
            }

            return $team->save();
        });
    }

    public function deleteTeam(int $id): bool
    {
        $team = $this->find($id);

        return DB::transaction(function() use($team) {

            return $team->delete();
        });
    }

    public function allTeams(array $userFilters = []): Paginate
    {
        $teams = $this->model->query()->select('id', 'name', 'agegroup_id')->orderBy('name');

        $filters = array_merge($this->defaultTeamListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        $maxPerPage = is_null($userFilters['max_team_per_page']) ? $teams->count() : $filters['max_team_per_page'];

        return new Paginate($teams, $maxPerPage, $filters['page'], 'teams');
    }
}
