<?php

namespace App\Modules\Item;

use App\Models\Category;
use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Utility\Pagination\Paginate;
use Illuminate\Database\Eloquent\Collection;

interface ItemServiceInterface
{
    /**
     * Retrieve a list of items
     * 
     * @param $filters List of filters available to be applied'
     * 
     * @return Paginate<Item>
     */
    public function listItems(array $filters = []): Paginate;

    /**
     * Retrieve an Item
     * 
     * @param int $id
     * 
     * @return Item
     */
    public function retrieveItem(int $id): Item;

    /**
     * Create a new Item
     * 
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price
     * @param float $saleprice Cent value of the item sale price
     * @param int $stock Number of items on stock. Cannot be below 0.
     * @param null|bool $isFeatured Mark item as featured.
     * @param null|bool $isRRP
     * @param null|bool $isOnSale
     * @param null|bool $isHideOutOfStock
     * @param array<UploadedFile> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
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
     * @param null|float $saleprice Cent value of the item price
     * @param null|int $stock Number of items on stock. Cannot be below 0.
     * @param null|bool $isFeatured Mark item as featured.
     * @param null|bool $isRRP Mark item as featured.
     * @param null|bool $isOnSale Mark item as featured.
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
     * Update an existing Item
     * 
     * @param int $id
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price
     * @param int $stock Number of items on stock. Cannot be below 0.
     * @param null|bool $isFeatured Mark item as featured.
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
     * Delete an existing Item
     * 
     * @param User $initiator The user who initiated the delete command
     * @param Item $item The item to be deleted
     * 
     * @return bool
     */
    public function deleteItem(User $initiator, Item $item): bool;

    /**
     * List item discount code
     * 
     * @return Collection<Category>
     */
    public function listDiscountCode(): Collection;

     /**
     * Get total number of item news
     * 
     * @return int
     */
    public function countDiscountCode(): int;

    public function discountCodeItems(array $filters = []): Paginate;

    public function countItems();
}
