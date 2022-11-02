<?php

namespace App\Modules\Item;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ItemService implements ItemServiceInterface
{
    /**
     * Item Repository
     * 
     * @var ItemRepositoryInterface $itemRepository
     */
    protected ItemRepositoryInterface $itemRepository;

    /**
     * Category Repository
     * 
     * @var CategoryRepositoryInterface $categoryRepository
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * Tag Repository
     * 
     * @var TagRepositoryInterface $tagRepository
     */
    protected TagRepositoryInterface $tagRepository;

    public function __construct(ItemRepositoryInterface $itemRepository, CategoryRepositoryInterface $categoryRepository, TagRepositoryInterface $tagRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
    }

    public function listItems(array $filters = []): Paginate
    {
        return $this->itemRepository->listItems($filters);
    }

    public function retrieveItem(int $id): Item
    {
        return $this->itemRepository->retrieveItem($id);
    }

    public function createItem(string $title, string $description, float $price, int $stock, array $media, array $categories, array $tags): Item
    {
        return $this->itemRepository->createItem($title, $description, $price, $stock, $media, $categories, $tags);
    }

    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?int $stock, ?array $media, ?array $categories, ?array $tags): Item
    {
        return $this->itemRepository->duplicateItem($id, $title, $description, $price, $stock, $media, $categories, $tags);
    }

    public function updateItem(int $id, string $title, string $description, float $price, int $stock, ?array $media, array $categories, array $tags): bool
    {
        return $this->itemRepository->updateItem($id, $title, $description, $price, $stock, $media, $categories, $tags);
    }

    public function decreaseStocks(int $id, int $amount, bool $override = false): bool
    {
        return $this->itemRepository->decreaseStocks($id, $amount, $override);
    }

    public function deleteItem(User $initiator, Item $item): bool
    {
        return $this->itemRepository->deleteItem($item->id);
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

    public function listTags(): Collection
    {
        return $this->tagRepository->list();
    }

    public function retrieveTag(int $id): Tag
    {
        // placeholder
        return new Tag();
    }

    public function createTag(string $name): Tag
    {
        // placeholder
        return new Tag();
    }

    public function updateTag(int $Id, string $name): bool
    {
        // placeholder
        return false;
    }

    public function deleteTag(User $initiator, int $Id): bool
    {
        // placeholder
        return false;
    }

}