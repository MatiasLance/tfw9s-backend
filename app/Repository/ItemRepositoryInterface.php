<?php

namespace App\Repository;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Modules\Utility\Pagination\Paginate;
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
    public const PLACEHOLDER_IMAGE = 'brand_item_placeholder_thumbnail.jpg';

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
     * <code>
     * The $elements argument must be structured as below:
     *  $elements = [
     *      'element_id'    => (int) Required. ID of the element to use,
     *      'thumbnail_type'=> (string) Optional. When a value is provided, overrides the Element thumbnail type
     *      'thumbnail'     => (string) (Conditionally required). When thumbnail_type value is given, this value must be provided as well.
     *                          Dictates the value of the value of the thumbnail according to the given thumbnail_type given.
     *      'order'         => (int) Optional. The higher the number the further down the list of elements
     *  ]
     * </code>
     * 
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price. This will be the default item price. Item price can be overriden by item element.
     * @param array $elements Associative array of elements available for this item. See above for the detailed structure
     * @param array<UploadedFile> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param array<Tags> $tags Array of tags that this item will have
     * 
     * @return Item
     */
    public function createItem(string $title, string $description, float $price, array $elements, array $media, array $categories, array $tags): Item;

    /**
     * Duplicate an existing Item. Pass null to retain the value from the original item.
     * 
     * @param int $id
     * @param null|string $title
     * @param null|string $description
     * @param null|float $price Cent value of the item price
     * @param null|array $elements Associative array of elements available for this item. See above for the detailed structure
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param null|array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param null|array<Tags> $tags Array of tags that this item will have
     */
    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?array $elements, ?array $media, ?array $categories, ?array $tags): Item;

    /**
     * Update an existing Item instance
     * 
     * @param int $id
     * @param string $title
     * @param string $description
     * @param float $price Cent value of the item price
     * @param array $elements Associative array of elements available for this item. See above for the detailed structure
     * @param null|array<UploadedFile|string> $media List of media for the Item
     * @param array<Category> $categories Categories on which this item will be under. Can be empty for uncategorized items.
     * @param array<Tags> $tags Array of tags that this item will have
     * 
     * @return bool
     */
    public function updateItem(int $id, string $title, string $description, float $price, array $elements, ?array $media, array $categories, array $tags): bool;

    /**
     * Create a new Item Unit
     * 
     * @param int $itemId ID of the item to put the item unit under
     * @param array $elementIds List of element ids that form the combination for the Item unit
     * @param null|float $price (Optional) When a value is given, overrides the item price
     * @param int $stock Number of stocks available for this Item unit
     * @param null|string $sku (Optional) SKU of the item unit
     * 
     * @return ItemUnit
     */
    public function createItemUnit(int $itemId, array $elementIds, ?float $price, int $stock = 0, ?string $sku = null): ItemUnit;

    /**
     * Update an existing item unit
     * 
     * @param int $itemId Id of the item the item unit is under
     * @param int $unitId Id of the item unit to update
     * @param null|array $elementIds List of element ids that form the combination for the Item unit
     * @param null|float $price (Optional) When a value is given, overrides the item price
     * @param null|int $stock Number of stocks available for this Item unit
     * @param null|string $sku (Optional) SKU of the item unit
     * 
     * @return bool
     */
    public function updateItemUnit(int $itemId, int $unitId, ?array $elementIds, ?float $price, ?int $stock = 0, ?string $sku = null): bool;

    /**
     * Delete an existing Item Unit
     * 
     * @param int $itemId The Id of the item that the Item unit is under
     * @param int $unitId Id of the Item unit to delete
     * 
     * @return bool
     */
    public function deleteItemUnit(int $itemId, int $unitId): bool;
    
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
}