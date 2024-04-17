<?php

namespace App\Modules\Manager;

use App\Models\User;
use App\Models\Manager;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\ManagerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ManagerService implements ManagerServiceInterface
{
    /**
     * Manager Repository
     *
     * @var ManagerRepositoryInterface $managerRepository
     */
    protected ManagerRepositoryInterface $managerRepository;

    public function __construct(ManagerRepositoryInterface $managerRepository)
    {
        $this->managerRepository = $managerRepository;
    }

    public function listManagers(array $filters = []): Paginate
    {
        return $this->managerRepository->listManagers($filters);
    }

    public function retrieveManager(int $id): Manager
    {
        return $this->managerRepository->retrieveManager($id);
    }

    public function createManager($first_name, $last_name, $mobile, $email, $description): Manager
    {
        return $this->managerRepository->createManager($first_name, $last_name, $mobile, $email, $description);
    }

    public function updateManager(int $id, string $first_name, string $last_name, string $mobile, string $email, string $description): bool
    {
        return $this->managerRepository->updateManager($id, $first_name, $last_name, $mobile, $email, $description);
    }

    public function deleteManager(User $initiator, Manager $manager): bool
    {
        return $this->managerRepository->deleteManager($manager->id);
    }
}
