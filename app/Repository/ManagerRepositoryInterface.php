<?php

namespace App\Repository;

use App\Models\Manager;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ManagerRepositoryInterface
{
    /**
     * Maximum managers to be shown per page
     *
     * @var int MAX_PAGE_managerS
     */
    public const MAX_PAGE_MANAGERS = 12;

    /**
     * Placeholder manager name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_manager_placeholder_thumbnail.jpg';


    /**
     * Retrieve a list of managers.
     *
     * @param array $userFilters
     *
     * @return Paginate<manager>
     */
    public function listmanagers(array $userFilters = []): Paginate;

    /**
     * Retrieve an manager
     *
     * @param int $id
     *
     * @return manager
     */
    public function retrievemanager(int $id): Manager;

    /**
     * Create a new manager instance
     *
     * @param int $first_name
     * @param string $last_name
     * @param string $mobile
     * @param int $email
     * @param int $description
     *
     * @return manager
     */
    public function createmanager(string $first_name, string $last_name, string $mobile, string $email, string $description): Manager;

    /**
     * Update an existing manager instance
     *
     * @param int $id
     * @param int $first_name
     * @param string $last_name
     * @param string $mobile
     * @param int $email
     * @param int $description
     *
     * @return bool
     */
    public function updatemanager(int $id, string $first_name, string $last_name, string $mobile, string $email, string $description): bool;

    /**
     * Delete an existing manager instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deletemanager(int $id): bool;

}
