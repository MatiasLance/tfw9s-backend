<?php

namespace App\Modules\Region;

use App\Models\User;
use App\Models\Region;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\RegionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RegionService implements RegionServiceInterface
{
    /**
     * Region Repository
     *
     * @var RegionRepositoryInterface $regionRepository
     */
    protected RegionRepositoryInterface $regionRepository;

    public function __construct(RegionRepositoryInterface $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    public function listRegions(array $filters = []): Paginate
    {
        return $this->regionRepository->listRegions($filters);
    }

    public function retrieveRegion(int $id): Region
    {
        return $this->regionRepository->retrieveRegion($id);
    }

    public function createRegion($name, $description): region
    {
        return $this->regionRepository->createRegion($name, $description);
    }

    public function updateRegion(int $id, string $name, string $description): bool
    {
        return $this->regionRepository->updateRegion($id, $name, $description);
    }

    public function deleteRegion(User $initiator, Region $region): bool
    {
        return $this->regionRepository->deleteRegion($region->id);
    }
}
