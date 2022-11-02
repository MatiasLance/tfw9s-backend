<?php

namespace App\Repository\Eloquent\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * class BaseRepository
 * @package App\Repository\Eloquent
 * 
 * Base Class for Eloquent Repositories
 */
class BaseRepository implements EloquentRepositoryInterface
{
    /**
     * @var Model $model
     */
    protected $model;

    /**
     * BaseRepository Constructor
     * 
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieves all the instances of Model
     * 
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a Model instance using ID
     * 
     * @param int $id
     * 
     * @return null|Model
     */
    public function find(int $id): ?Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Delete a Model instance
     * 
     * @param int $id
     * 
     * @return bool
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function() use($id) {
            $instance = $this->find($id);
            return $instance->delete();
        });
    }
}