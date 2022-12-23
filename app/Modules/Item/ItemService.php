<?php

namespace App\Modules\Item;

use App\Models\Item;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\TagRepositoryInterface;

class ItemService implements ItemServiceInterface
{
    /**
     * Item Repository
     * 
     * @var ItemRepositoryInterface $itemRepository
     */
    protected ItemRepositoryInterface $itemRepository;

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

    public function createItem(string $title, string $description, float $price, int $stock, bool $isFeatured, array $media, array $categories, array $tags): Item
    {
        return $this->itemRepository->createItem($title, $description, $price, $stock, $isFeatured, $media, $categories, $tags);
    }

    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?int $stock, bool $isFeatured, ?array $media, ?array $categories, ?array $tags): Item
    {
        return $this->itemRepository->duplicateItem($id, $title, $description, $price, $stock, $isFeatured, $media, $categories, $tags);
    }

    public function addItemVariant(int $id, ?string $title, ?string $description, ?float $price, ?int $stock, bool $isFeatured, ?array $media, ?array $categories, ?array $tags): Item
    {
        return $this->itemRepository->addItemVariant($id, $title, $description, $price, $stock, $isFeatured, $media, $categories, $tags);
    }
    public function updateItem(int $id, string $title, string $description, float $price, int $stock, bool $isFeatured, ?array $media, array $categories, array $tags): bool
    {
        return $this->itemRepository->updateItem($id, $title, $description, $price, $stock, $isFeatured, $media, $categories, $tags);
    }

    public function decreaseStocks(int $id, int $amount, bool $override = false): bool
    {
        return $this->itemRepository->decreaseStocks($id, $amount, $override);
    }

    public function deleteItem(User $initiator, Item $item): bool
    {
        return $this->itemRepository->deleteItem($item->id);
    }
}
