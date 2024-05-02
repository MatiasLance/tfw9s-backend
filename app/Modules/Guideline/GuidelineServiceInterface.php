<?php

namespace App\Modules\Guideline;

use App\Models\Guideline;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface GuidelineServiceInterface
{
    /**
     * Retrieve a list of guidelines
     *
     * @param $filters List of filters available to be applied'
     *
     * @return Paginate<Guideline>
     */
    public function listGuidelines(array $filters = []): Paginate;

    /**
     * Retrieve an Guideline
     *
     * @param int $id
     *
     * @return Guideline
     */
    public function retrieveGuideline(int $id): Guideline;

    /**
     * Create a new Guideline
     *
     * @param string $type
     * @param string $content
     *
     * @return Guideline
     */
    public function createGuideline(string $type, string $content): Guideline;

    /**
     * Update an existing Guideline
     *
     * @param int $id
     * @param string $type
     * @param string $content
     *
     * @return bool
     */
    public function updateGuideline(int $id, string $type, string $content): bool;

    /**
     * Update an existing Guideline
     *
     * @param int $id
     *
     * @return bool
     */
    public function setActive(int $id): bool;

    /**
     * Update an existing Guideline
     *
     * @param int $id
     *
     * @return bool
     */
    public function deactivate(int $id): bool;

    /**
     * Delete an existing Guideline
     *
     * @param User $initiator The user who initiated the delete command
     * @param Guideline $guideline The guideline to be deleted
     *
     * @return bool
     */
    public function deleteGuideline(User $initiator, Guideline $guideline): bool;

}
