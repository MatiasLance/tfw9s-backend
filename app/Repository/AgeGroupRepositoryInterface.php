<?php

namespace App\Repository;

use App\Models\AgeGroup;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface AgeGroupRepositoryInterface
{
    /**
     * Maximum ageGroups to be shown per page
     *
     * @var int MAX_PAGE_ageGroups
     */
    public const MAX_PAGE_AGEGROUPS = 12;

    /**
     * Placeholder ageGroup name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_ageGroup_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of ageGroups.
     *
     * @param array $userFilters
     *
     * @return Paginate<ageGroup>
     */
    public function listAgeGroup(array $userFilters = []): Paginate;

    /**
     * Retrieve an ageGroup
     *
     * @param int $id
     *
     * @return ageGroup
     */
    public function retrieveageGroup(int $id): AgeGroup;

    /**
     * Create a new ageGroup instance
     *
     * @param string $name
     * @param int $min_age
     * @param int $max_age
     *
     * @return ageGroup
     */
    public function createageGroup(string $name, int $min_age, int $max_age): AgeGroup;

    /**
     * Update an existing ageGroup instance
     *
     * @param int $id
     * @param string $name
     * @param int $min_age
     * @param int $max_age
     *
     * @return bool
     */
    public function updateageGroup(int $id, string $name, int $min_age, int $max_age): bool;

    /**
     * Delete an existing ageGroup instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteageGroup(int $id): bool;

}
