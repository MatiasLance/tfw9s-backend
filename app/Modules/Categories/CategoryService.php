<?php

namespace App\Modules\Categories;

use App\Models\Category;
use App\Models\User;
use App\Repository\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService implements CategoryServiceInterface
{
    /**
     * Category Repository
     * 
     * @var CategoryRepositoryInterface $categoryRepository
     */
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function listCategories(): Collection
    {
        return $this->categoryRepository->list();
    }

    public function countCategories(): int
    {
        return $this->categoryRepository->countTotal();
    }

    public function retrieveCategory(int $id): Category
    {
        return $this->categoryRepository->retrieve($id);
    }

    public function createCategory(string $name, ?int $parentId = null): Category
    {
        return $this->categoryRepository->create($name, $parentId);
    }

    public function updateCategory(int $id, string $name, ?int $parentId = null): bool
    {
        return $this->categoryRepository->update($id, $name, $parentId);
    }

    public function moveCategory(array $categories, ?int $target): bool
    {
        return $this->categoryRepository->move($categories, $target);
    }

    /**
     * @todo Record delete initiator
     */
    public function deleteCategory(User $initiator, int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}