<?php

namespace App\Repository;

use App\Models\Region;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface RegionRepositoryInterface
{
    /**
     * Maximum regions to be shown per page
     *
     * @var int MAX_PAGE_REGIONS
     */
    public const MAX_PAGE_REGIONS = 12;

    /**
     * Placeholder region name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_region_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of regions.
     *
     * @param array $userFilters
     *
     * @return Paginate<Region>
     */
    public function listRegions(array $userFilters = []): Paginate;

    /**
     * Retrieve an Region
     *
     * @param int $id
     *
     * @return Region
     */
    public function retrieveRegion(int $id): Region;

    /**
     * Create a new region instance
     *
     * @param string $name
     * @param string $description
     *
     * @return Region
     */
    public function createRegion(string $name, string $description): region;

    /**
     * Update an existing Region instance
     *
     * @param int $id
     * @param string $name
     * @param string $description
     *
     * @return bool
     */
    public function updateRegion(int $id, string $name, string $description): bool;

    /**
     * Delete an existing region instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteRegion(int $id): bool;

}
