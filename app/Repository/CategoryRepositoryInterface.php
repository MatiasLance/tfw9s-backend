<?php

namespace App\Repository;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Retrieve list of all categories
     * 
     * @return Collection<Category>
     */
    public function list(): Collection;

    /**
     * Retrieve total count of all categories
     * 
     * @return int
     */
    public function countTotal(): int;

    /**
     * Retrieve an existing Category
     * 
     * @param int $id
     * 
     * @return Category
     */
    public function retrieve(int $id): Category;

    /**
     * Create a new category
     * 
     * @param string    $name
     * @param null|int  $parentId
     * 
     * @return Category
     */
    public function create(string $name, ?int $parentId = null): Category;
    
    /**
     * Update an existing category
     * 
     * @param int       $id         ID of the category to change
     * @param string    $name       New name of the category
     * @param null|int  $parentId   (Optional) Id of the parent, if has any.
     * 
     * @return bool
     */
    public function update(int $id, string $name, ?int $parentId = null): bool;

    /**
     * Move categories under another category or to the root
     * 
     * @param array<int>    $categories Ids of the category to move
     * @param null|int      $target     ID of the target category. When null, categories are set as root
     * 
     * @return bool
     */
    public function move(array $categories, ?int $target): bool;

    /**
     * Delete an existing category
     * 
     * @param int $id ID of the category to delete
     * 
     * @return bool
     */
    public function delete(int $id): bool;

public function countCategory();
}