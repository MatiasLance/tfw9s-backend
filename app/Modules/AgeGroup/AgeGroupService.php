<?php

namespace App\Modules\AgeGroup;

use App\Models\User;
use App\Models\AgeGroup;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\AgeGroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AgeGroupService implements AgeGroupServiceInterface
{
    /**
     * AgeGroup Repository
     *
     * @var AgeGroupRepositoryInterface $ageGroupRepository
     */
    protected AgeGroupRepositoryInterface $ageGroupRepository;

    public function __construct(AgeGroupRepositoryInterface $ageGroupRepository)
    {
        $this->ageGroupRepository = $ageGroupRepository;
    }

    public function listAgeGroup(array $filters = []): Paginate
    {
        return $this->ageGroupRepository->listAgeGroup($filters);
    }

    public function retrieveAgeGroup(int $id): AgeGroup
    {
        return $this->ageGroupRepository->retrieveAgeGroup($id);
    }

    public function createAgeGroup($name, $min_age, $max_age): AgeGroup
    {
        return $this->ageGroupRepository->createAgeGroup($name, $min_age, $max_age);
    }

    public function updateAgeGroup(int $id, string $name, $min_age, $max_age): bool
    {
        return $this->ageGroupRepository->updateAgeGroup($id, $name, $min_age, $max_age);
    }

    public function deleteAgeGroup(User $initiator, AgeGroup $ageGroup): bool
    {
        return $this->ageGroupRepository->deleteAgeGroup($ageGroup->id);
    }
}
