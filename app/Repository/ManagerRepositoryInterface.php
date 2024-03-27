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
     * @param int $user_id
     * @param string $date_of_birth
     * @param string $address
     * @param int $age
     *
     * @return manager
     */
    public function createmanager(int $user_id, string $date_of_birth, string $address, int $age): Manager;

    /**
     * Update an existing manager instance
     *
     * @param int $id
     * @param int $user_id
     * @param string $date_of_birth
     * @param string $address
     * @param int $age
     *
     * @return bool
     */
    public function updatemanager(int $id, int $user_id, string $date_of_birth, string $address, int $age): bool;

    /**
     * Delete an existing manager instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deletemanager(int $id): bool;

}
