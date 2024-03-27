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

    public function createManager($user_id, $date_of_birth, $address, $age): Manager
    {
        return $this->managerRepository->createManager($user_id, $date_of_birth, $address, $age);
    }

    public function updateManager(int $id, int $user_id, string $date_of_birth, string $address, int $age): bool
    {
        return $this->managerRepository->updateManager($id, $user_id, $date_of_birth, $address, $age);
    }

    public function deleteManager(User $initiator, Manager $manager): bool
    {
        return $this->managerRepository->deleteManager($manager->id);
    }
}
