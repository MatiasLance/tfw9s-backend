<?php

namespace App\Repository;

use App\Models\Category;
use App\Models\Item;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface ItemRepositoryInterface
{
    /**
     * Maximum items to be shown per page
     *
     * @var int MAX_PAGE_ITEMS
     */
    public const MAX_PAGE_ITEMS = 12;

    /**
     * Placeholder image name
     *
     * @var string PLACEHOLDER_IMAGE
     */
    public const PLACEHOLDER_IMAGE = 'brand_item_placeholder_thumbnail.png';

    /**
     * Retrieve a list of items.
     *
     * @param array $userFilters
     *
     * @return Paginate<Item>
     */
    public function listItems(array $userFilters = []): Paginate;

    /**
     * Retrieve an Item
     *
     * @param int $id
     *
     * @return Item
     */
    public function retrieveItem(int $id): Item;

    /**
     * Create a new item instance
     *
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price
     * @param float $saleprice Cent value of the item price
     * @param int $stock Number of items on stock. Cannot be below 0.
     * @param bool $isFeatured Mark item as featured.
     * @param bool $isRRP Mark toggle for RRP on that Item.
     * @param bool $isOnSale Mark toggle for on sale on that Item.
     * @param array<UploadedFile> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param string $shippingId
     * @param array<Tags> $tags Array of tags that this item will have
     *
     * @return Item
     */
    public function createItem(string $title, string $description, float $price, float $saleprice, int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, bool $isHideOutOfStock, array $media, array $categories, string $shippingId, array $tags): Item;

    /**
     * Duplicate an existing Item. Pass null to retain the value from the original item.
     *
     * @param int $id
     * @param null|string $title
     * @param null|string $description
     * @param null|float $price Cent value of the item price
     * @param null|int $stock Number of items on stock. Cannot be below 0.
     * @param null|bool $isFeatured Mark item as featured.
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param null|array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param null|array<Tags> $tags Array of tags that this item will have
     */
    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?float $saleprice, ?int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, ?array $media, ?array $categories, ?array $tags): Item;

    /**
     * Add another item as a variant of an existing Item. Arguments can be optionally passed to overwrite parent item values
     *
     * @param int $id Parent item ID
     * @param null|string $title
     * @param null|string $description
     * @param null|float $price Cent value of the item price
     * @param null|int $stock Number of items on stock. Cannot be below 0.
     * @param null|bool $isFeatured Mark item as featured.
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param null|array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param null|array<Tags> $tags Array of tags that this item will have
     */
    public function addItemVariant(int $id, ?string $title, ?string $description, ?float $price, ?float $saleprice, ?int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, bool $isHideOutOfStock, ?array $media, ?array $categories, ?array $tags): Item;

    /**
     * Update an existing Item instance
     *
     * @param int $id
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price
     * @param int $stock Number of items on stock. Cannot be below 0.
     * @param bool $isFeatured Mark item as featured.
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param string $shippingId
     * @param array<Tags> $tags Array of tags that this item will have
     *
     * @return bool
     */
    public function updateItem(int $id, string $title, string $description, float $price, float $saleprice, int $stock, bool $isFeatured, bool $isRRP, bool $isOnSale, bool $isHideOutOfStock, ?array $media, array $categories, string $shippingId, array $tags): bool;

    /**
     * Decrease the stocks of an item. Useful when Item is bought by a customer
     *
     * @param int $id
     * @param int $amount
     * @param bool $override When true, decrease the stocks to 0 even if the amount is greater than current stocks. This will still throw an exception.
     *
     * @return bool
     */
    public function decreaseStocks(int $id, int $amount, bool $override = false): bool;

    /**
     * Delete an existing item instance
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteItem(int $id): bool;

    /**
     * Retrieve a list of discount codes.
     *
     * @param array $userFilters
     *
     * @return Paginate<Item>
     */
    public function discountCodeItems(array $userFilters = []): Paginate;

    /**
     * Retrieve list of all discount codes
     *
     * @return Collection<Category>
     */
    public function listDiscountCode(): Collection;
    /**
     * Retrieve total count of all discount codes
     *
     * @return int
     */
    public function totalDiscountCode(): int;
}
