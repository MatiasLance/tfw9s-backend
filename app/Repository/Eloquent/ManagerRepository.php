<?php

namespace App\Repository\Eloquent;

use App\Models\Manager;
use App\Modules\Manager\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\ManagerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Modules\User\UserServiceInterface;

class ManagerRepository extends BaseRepository implements ManagerRepositoryInterface
{
    protected UserServiceInterface $userService;
    /**
     * Storage Module
     *
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    /**
     * Default filters for retrieving list of managers
     *
     * @var array $defaultManagerListFilters
     */
    protected array $defaultManagerListFilters = [
        /**
         * Search keyword
         * This filters the managers with a keyword. When this value is null, this filter is skipped.
         */
        'user' => null,

        /**
         * Search keyword
         * This filters the managers with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Sort
         * Sorts the managers according to this value. By default, will sort the managers by their creation date.
         * For the available sort values, check App\Modules\Manager\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of managers to get
         */
        'page' => 1,

        /**
         * Max manager per page
         *
         * Maximum number of managers shown per page. When 0 or null is passed, will get every manager
         */
        'max_manager_per_page' => self::MAX_PAGE_MANAGERS,
    ];

    public function __construct(Manager $manager, StorageInterface $storageService, UserServiceInterface $userService)
    {
        parent::__construct($manager);
        $this->userService = $userService;
        $this->storageService = $storageService;
    }

    public function listManagers(array $userFilters = []): Paginate
    {
        $managers = $this->model->query();

        $filters = array_merge($this->defaultManagerListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $managers = $managers->whereHas('user', function ($q) use ($filters) {
                $q->where('first_name', 'LIKE', '%' . $filters['q'] . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        if (!is_null($filters['user'])) {
            $managers = $managers->whereHas('user', function ($q) use ($filters) {
                $q->where('id', '=', $filters['user']);
            });
        }

        switch ($filters['sort']) {
            case Filter::SORT_A_TO_Z:
                $managers = $managers->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $managers = $managers->orderByDesc('name');
                break;

            default:
                $managers = $managers->orderBy('created_at');
                break;
        }

        return new Paginate($managers, $filters['max_manager_per_page'], $filters['page'], 'managers');
    }

    public function retrieveManager(int $id): manager
    {
        return $this->find($id);
    }

    public function createManager(string $first_name, string $last_name, string $mobile, string $email, string $description): manager
    {

        $password = bcrypt('thefinalwhistle123');
        $user = $this->userService->create($email, $first_name, $last_name, $mobile, $password);
        $user->assignRole('manager');

        $manager = new Manager();
        $manager->description = $description;

        return DB::transaction(function() use($manager, $user) {
            $user->save();

            $manager->user_id = $user->id;
            $manager->save();

            return $manager;
        });
    }

    public function updateManager(int $id, string $first_name, string $last_name, string $mobile, string $email, string $description): bool
    {
        $manager = $this->find($id);
        $user = $manager->user;
        $manager->description = $description;

        $user = $this->userService->update($user, $first_name, $last_name, $email, $mobile);

        return DB::transaction(function() use($manager) {

            return $manager->save();
        });
    }

    public function deleteManager(int $id): bool
    {
        $manager = $this->find($id);

        return DB::transaction(function() use($manager) {

            $user = $manager->user;

            if ($user) {
                $user->delete();
            }

            return $manager->delete();
        });
    }
}
