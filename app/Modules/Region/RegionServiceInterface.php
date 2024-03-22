<?php

namespace App\Modules\Region;

use App\Models\Region;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface RegionServiceInterface
{
    /**
     * Retrieve a list of regions
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Region>
     */
    public function listRegions(array $filters = []): Paginate;

    /**
     * Retrieve an Region
     *
     * @param int $id
     *
     * @return Region
     */
    public function retrieveRegion(int $id): Region;

    /**
     * Create a new Region
     *
     * @param string $name
     * @param string $description
     *
     * @return Region
     */
    public function createRegion(string $name, string $description): Region;

    /**
     * Update an existing Region
     *
     * @param int $id
     * @param string $name
     * @param string $description
     *
     * @return bool
     */
    public function updateRegion(int $id, string $name, string $description): bool;

    /**
     * Delete an existing Region
     *
     * @param User $initiator The user who initiated the delete command
     * @param Region $region The region to be deleted
     *
     * @return bool
     */
    public function deleteRegion(User $initiator, Region $region): bool;

}
