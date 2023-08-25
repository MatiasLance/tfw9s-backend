<?php

namespace App\Modules\Item;

use App\Models\Item;
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

    protected CategoryRepositoryInterface $categoryRepository;

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

    public function createItem(string $title, string $description, float $price, float $saleprice, int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, bool $isHideOutOfStock, array $media, array $categories, string $shippingId, array $tags): Item
    {
        return $this->itemRepository->createItem($title, $description, $price, $saleprice, $stock, $isFeatured, $isRRP, $isOnSale, $isHideOutOfStock, $media, $categories, $shippingId, $tags);
    }

    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?float $saleprice, ?int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, ?array $media, ?array $categories, ?array $tags): Item
    {
        return $this->itemRepository->duplicateItem($id, $title, $description, $price, $saleprice, $stock, $isFeatured, $isRRP, $isOnSale, $media, $categories, $tags);
    }

    public function addItemVariant(int $id, ?string $title, ?string $description, ?float $price, ?int $stock, bool $isFeatured, bool $isHideOutOfStock, ?array $media, ?array $categories, ?array $tags): Item
    {
        return $this->itemRepository->addItemVariant($id, $title, $description, $price, $stock, $isFeatured, $isHideOutOfStock, $media, $categories, $tags);
    }
    public function updateItem(int $id, string $title, string $description, float $price, float $saleprice, int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, bool $isHideOutOfStock, ?array $media, array $categories, string $shippingId, array $tags): bool
    {
        return $this->itemRepository->updateItem($id, $title, $description, $price, $saleprice, $stock, $isFeatured, $isRRP, $isOnSale, $isHideOutOfStock, $media, $categories, $shippingId, $tags);
    }

    public function decreaseStocks(int $id, int $amount, bool $override = false): bool
    {
        return $this->itemRepository->decreaseStocks($id, $amount, $override);
    }

    public function deleteItem(User $initiator, Item $item): bool
    {
        return $this->itemRepository->deleteItem($item->id);
    }

    public function listDiscountCode(): Collection
    {
        return $this->itemRepository->listDiscountCode();
    }

    public function countDiscountCode(): int
    {
        return $this->itemRepository->totalDiscountCode();
    }

    public function discountCodeItems(array $filters = []): Paginate
    {
        return $this->itemRepository->discountCodeItems($filters);
    }
}
