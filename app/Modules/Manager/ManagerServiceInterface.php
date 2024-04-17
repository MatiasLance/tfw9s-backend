<?php

namespace App\Modules\Manager;

use App\Models\Manager;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface ManagerServiceInterface
{
    /**
     * Retrieve a list of managers
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Manager>
     */
    public function listManagers(array $filters = []): Paginate;

    /**
     * Retrieve an Manager
     *
     * @param int $id
     *
     * @return Manager
     */
    public function retrieveManager(int $id): Manager;

    /**
     * Create a new Manager
     *
     * @param int $first_name
     * @param string $last_name
     * @param string $mobile
     * @param int $email
     * @param int $description
     *
     * @return Manager
     */
    public function createManager(string $first_name, string $last_name, string $mobile, string $email, string $description): Manager;

    /**
     * Update an existing Manager
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
    public function updateManager(int $id, string $first_name, string $last_name, string $mobile, string $email, string $description): bool;

    /**
     * Delete an existing Manager
     *
     * @param User $initiator The user who initiated the delete command
     * @param Manager $manager The manager to be deleted
     *
     * @return bool
     */
    public function deleteManager(User $initiator, Manager $manager): bool;

}
