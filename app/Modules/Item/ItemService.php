<?php

namespace App\Modules\Item;

use App\Models\Item;
use App\Models\User;
use App\Modules\Currency\Traits\HandlesCurrency;
use App\Modules\Utility\Pagination\Paginate;
use App\Modules\Variants\VariantServiceInterface;
use App\Repository\ItemRepositoryInterface;

class ItemService implements ItemServiceInterface
{
    use HandlesCurrency;

    /**
     * Item Repository
     * 
     * @var ItemRepositoryInterface $itemRepository
     */
    protected ItemRepositoryInterface $itemRepository;

    protected VariantServiceInterface $variantService;

    public function __construct(ItemRepositoryInterface $itemRepository, VariantServiceInterface $variantService)
    {
        $this->itemRepository = $itemRepository;
        $this->variantService = $variantService;
    }

    public function listItems(array $filters = []): Paginate
    {
        return $this->itemRepository->listItems($filters);
    }

    public function retrieveItem(int $id)
    {
        $item = $this->itemRepository->retrieveItem($id);
        $variants = $this->variantService->formatItemElements($item->elements->toArray());
        return [
            'item' => $item,
            'variants' => $variants
        ];
    }

    public function createItem(string $title, string $description, $price, $elements, array $media, array $categories, array $tags): Item
    {
        // @todo make repository take only int for price
        // if (is_float($price)) {
        //     $price = $this->toCent($price);
        // }

        $paddedElements = [];
        foreach ($elements as $element) {
            $paddedElement = array_merge($element, self::DEFAULT_ELEMENT_VALUES);
            array_push($paddedElements, $paddedElement);
        }
        return $this->itemRepository->createItem($title, $description, $price, $paddedElements, $media, $categories, $tags);
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