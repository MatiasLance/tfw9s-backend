<?php

namespace App\Repository;

use App\Models\Guideline;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface GuidelineRepositoryInterface
{
    /**
     * Maximum Guidelines to be shown per page
     *
     * @var int MAX_PAGE_GuidelineS
     */
    public const MAX_PAGE_GUIDELINES = 12;

    /**
     * Placeholder Guideline name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_Guideline_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of Guidelines.
     *
     * @param array $userFilters
     *
     * @return Paginate<Guideline>
     */
    public function listGuidelines(array $userFilters = []): Paginate;

    /**
     * Retrieve an Guideline
     *
     * @param int $id
     *
     * @return Guideline
     */
    public function retrieveGuideline(int $id): Guideline;

    /**
     * Create a new Guideline instance
     *
     * @param int $user_id
     * @param string $date_of_birth
     * @param string $address
     * @param int $age
     *
     * @return Guideline
     */
    public function createGuideline(string $type, string $content): Guideline;

    /**
     * Update an existing Guideline instance
     *
     * @param int $id
     * @param int $user_id
     * @param string $date_of_birth
     * @param string $address
     * @param int $age
     *
     * @return bool
     */
    public function updateGuideline(int $id, string $type, string $content): bool;

    /**
     * Update an existing Guideline instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function setActive(int $id): bool;

    /**
     * Update an existing Guideline instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deactivate(int $id): bool;

    /**
     * Delete an existing Guideline instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteGuideline(int $id): bool;

}
