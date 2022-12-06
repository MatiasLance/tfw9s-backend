<?php

namespace App\Modules\Item;

use App\Models\Item;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\ItemRepositoryInterface;

class ItemService implements ItemServiceInterface
{
    /**
     * Item Repository
     * 
     * @var ItemRepositoryInterface $itemRepository
     */
    protected ItemRepositoryInterface $itemRepository;

    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
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
}