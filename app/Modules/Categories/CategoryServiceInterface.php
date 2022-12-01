<?php

namespace App\Modules\Categories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface
{
    /**
     * List item categories
     * 
     * @return Collection<Category>
     */
    public function listCategories(): Collection;

     /**
     * Get total number of item categories
     * 
     * @return int
     */
    public function countCategories(): int;

    /**
     * Retrieve an item category
     * 
     * @param int $id ID of category
     * 
     * @return Category
     */
    public function retrieveCategory(int $id): Category;

    /**
     * Create a new item Category
     * 
     * @param string    $name       Category name
     * @param null|int  $parentId   (Optional) Id of the parent, if has any.
     * 
     * @return Category
     */
    public function createCategory(string $name, ?int $parentId = null): Category;

    /**
     * Update an existing category
     * 
     * @param int       $id         ID of category
     * @param string    $name
     * @param null|int  $parentId   (Optional) Id of the parent, if has any.
     * 
     * @return bool
     */
    public function updateCategory(int $id, string $name, ?int $parentId = null): bool;

    /**
     * Move categories under another category or to the root
     * 
     * @param array<int>    $categories Ids of the category to move
     * @param null|int      $target     ID of the target category. When null, categories are set as root
     * 
     * @return bool
     */
    public function moveCategory(array $categories, ?int $target): bool;

    /**
     * Delete existing category
     * 
     * @param User $initiator The user who initiated the delete command
     * @param int $id ID of the category to be deleted
     * 
     * @return bool
     */
    public function deleteCategory(User $initiator, int $id): bool;
}