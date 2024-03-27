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

class ManagerRepository extends BaseRepository implements ManagerRepositoryInterface
{
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

    public function __construct(Manager $manager, StorageInterface $storageService)
    {
        parent::__construct($manager);
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

        $maxPerPage = is_null($userFilters['max_manager_per_page']) ? $managers->count() : $filters['max_manager_per_page'];

        return new Paginate($managers, $maxPerPage, $filters['page'], 'managers');
    }

    public function retrieveManager(int $id): manager
    {
        return $this->find($id);
    }

    public function createManager(int $user_id, string $date_of_birth, string $address, int $age): manager
    {
        $manager = new Manager();
        $manager->user_id = $name;
        $manager->date_of_birth = $date_of_birth;
        $manager->address= $address;
        $manager->age= $age;

        return DB::transaction(function() use($manager) {
            $manager->save();

            return $manager;
        });
    }

    public function updateManager(int $id, int $user_id, string $date_of_birth, string $address, int $age): bool
    {
        $manager = $this->find($id);
        $manager->user_id = $name;
        $manager->date_of_birth = $date_of_birth;
        $manager->address= $address;
        $manager->age= $age;

        return DB::transaction(function() use($manager) {

            return $manager->save();
        });
    }

    public function deleteManager(int $id): bool
    {
        $manager = $this->find($id);

        return DB::transaction(function() use($manager) {

            return $manager->delete();
        });
    }
}
