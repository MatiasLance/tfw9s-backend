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
     * @param int $stock Number of items on stock. Cannot be below 0.
     * @param array<UploadedFile> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param array<Tags> $tags Array of tags that this item will have
     * 
     * @return Item
     */
    public function createItem(string $title, string $description, float $price, int $stock, array $media, array $categories, array $tags): Item;

    /**
     * Duplicate an existing Item. Pass null to retain the value from the original item.
     * 
     * @param int $id
     * @param null|string $title
     * @param null|string $description
     * @param null|float $price Cent value of the item price
     * @param null|int $stock Number of items on stock. Cannot be below 0.
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param null|array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param null|array<Tags> $tags Array of tags that this item will have
     */
    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?int $stock, ?array $media, ?array $categories, ?array $tags): Item;

    /**
     * Update an existing Item
     * 
     * @param int $id
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price
     * @param int $stock Number of items on stock. Cannot be below 0.
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param array<Tags> $tags Array of tags that this item will have
     * 
     * @return bool
     */
    public function updateItem(int $id, string $title, string $description, float $price, int $stock, ?array $media, array $categories, array $tags): bool;

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

    /**
     * List all available item tags
     * 
     * @return Collection<Tag>
     */
    public function listTags(): Collection;

    /**
     * Retrieve an existing item tag
     * 
     * @param int $id ID of the item tag
     * 
     * @return Tag
     */
    public function retrieveTag(int $id): Tag;

    /**
     * Create a new item Tag
     * 
     * @param string $name
     * 
     * @return Tag
     */
    public function createTag(string $name): Tag;

    /**
     * Update an existing tag
     * 
     * @param int $id ID of the tag to update
     * @param string $name
     * 
     * @return bool
     */
    public function updateTag(int $Id, string $name): bool;

    /**
     * Delete an existing item tag
     * 
     * @param User $initiator
     * @param int $id ID of the tag to be deleted
     * 
     * @return bool
     */
    public function deleteTag(User $initiator, int $Id): bool;
}