<?php

namespace App\Repository\Eloquent\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * interface EloquentRepositoryInterface
 * @package App\Repository\Eloquent
 * 
 * Interface for Eloquen Repositories
 */
interface EloquentRepositoryInterface
{
    /**
     * Retrieves all the instances of Model
     * 
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Find a Model instance using ID
     * 
     * @param int $id
     * 
     * @return null|Model
     */
    public function find(int $id): ?Model;

    /**
     * Delete a Model instance
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function delete(int $id): bool;
}