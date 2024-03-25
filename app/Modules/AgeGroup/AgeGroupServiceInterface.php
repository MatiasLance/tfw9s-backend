<?php

namespace App\Modules\AgeGroup;

use App\Models\AgeGroup;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface AgeGroupServiceInterface
{
    /**
     * Retrieve a list of ageGroups
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<AgeGroup>
     */
    public function listAgeGroup(array $filters = []): Paginate;

    /**
     * Retrieve an AgeGroup
     *
     * @param int $id
     *
     * @return AgeGroup
     */
    public function retrieveAgeGroup(int $id): AgeGroup;

    /**
     * Create a new AgeGroup
     *
     * @param string $name
     * @param int $min_age
     * @param int $max_age
     *
     * @return AgeGroup
     */
    public function createAgeGroup(string $name, int $min_age, int $max_age): AgeGroup;

    /**
     * Update an existing AgeGroup
     *
     * @param int $id
     * @param string $name
     * @param int $min_age
     * @param int $max_age
     *
     * @return bool
     */
    public function updateAgeGroup(int $id, string $name, int $min_age, int $max_age): bool;

    /**
     * Delete an existing AgeGroup
     *
     * @param User $initiator The user who initiated the delete command
     * @param AgeGroup $ageGroup The ageGroup to be deleted
     *
     * @return bool
     */
    public function deleteAgeGroup(User $initiator, AgeGroup $ageGroup): bool;

}
