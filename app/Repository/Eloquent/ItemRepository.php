<?php

namespace App\Repository\Eloquent;

use App\Models\Category;
use App\Models\Item;
use App\Models\DiscountCode;
use App\Models\Tag;
use App\Models\Variant;
use App\Models\ItemVariant;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\Filter;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    /**
     * Storage Module
     * 
     * @var StorageInterface $storageService
     */
    protected StorageInterface $storageService;

    protected DiscountCode $discountCode;

    /**
     * Default filters for retrieving list of items
     * 
     * @var array $defaultItemListFilters
     */
    protected array $defaultItemListFilters = [
        /**
         * Search keyword
         * This filters the items with a keyword. When this value is null, this filter is skipped.
         */
        'q' => null,

        /**
         * Item and its variants
         * 
         * Pass an item ID to retrieve the Item and its variants. When null, the filter is skipped.
         */
        'item_variant' => null,

        /**
         * Featured items filter
         * 
         * When a boolean value is given, will filter items' featured status based on that value
         */
        'featured' => null,

        /**
         * Category filter
         * Filter items that are under the given category ID. Skipped when null.
         */
        'category' => null,

        /**
         * Tag filter
         * Filter items based on their tags. This should be an array of tag IDs. Skipped when null or empty.
         */
        'tags' => null,

        /**
         * Sort
         * Sorts the items according to this value. By default, will sort the items by their creation date.
         * For the available sort values, check App\Modules\Item\Filter
         */
        'sort' => Filter::SORT_LATEST,

        /**
         * Pagination
         * The current page of items to get
         */
        'page' => 1,

        /**
         * Max item per page
         * 
         * Maximum number of items shown per page. When 0 or null is passed, will get every item
         */
        'max_item_per_page' => self::MAX_PAGE_ITEMS,
        'size' => null,
        'min_price' => null,
        'max_price' => null,
        'in_stock' => null,
        'include_inactive' => false,
    ];

    public function __construct(
        Item $item,
        StorageInterface $storageService,
        DiscountCode $discountCode
    )
    {
        parent::__construct($item);
        $this->storageService = $storageService;
        $this->discountCode = $discountCode;
    }

    public function listItems(array $userFilters = []): Paginate
    {
        $areVariantsShown = false;
        $items = $this->model->query();

        $filters = array_merge($this->defaultItemListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Visibility must be applied before pagination so hidden products do not
        // affect totals, page counts, or the contents of a public page.
        if (! $filters['include_inactive']) {
            $items->visible();
        }

        // Search Filter
        if (!is_null($filters['q'])) {
            $items = $items->where('name', 'like', '%' . $filters['q'] . '%');
        }   

        // Featured Item Filter
        if (!is_null($filters['featured'])) {
            $areVariantsShown = true;
            $items = $items->where('is_featured', $filters['featured']);
        }

        // Category filter
        if (!is_null($filters['category'])) {
            $items = $items->whereHas('categories', function($q) use($filters) {
                $category = Category::find($filters['category']);
                if (!is_null($category)) {
                    $descendants = $category->descendants();
                    $q->whereIn('categories.id', $descendants);
                }
            });
        }

        // Tags filter
        if (!is_null($filters['tags'])) {
            $items = $items->whereHas('tags', function($q) use($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }

        // Item variant filter (for color variants using parent/child relationship)
        if (!is_null($filters['item_variant'])) {
            $areVariantsShown = true;
            $variantItem = $this->find($filters['item_variant']);
            
            $items = $items->where(function($q) use($variantItem){
                $q->where('id', $variantItem->id)
                    ->orWhere('parent_id', $variantItem->id);
            });
        }

        // NEW: Size variant filter
        if (!is_null($filters['size'])) {
            $areVariantsShown = true;
            $items = $items->whereHas('sizeVariants', function($q) use($filters) {
                $q->where('value', $filters['size'])
                ->where('stock_quantity', '>', 0);
            });
        }

        // NEW: Price range filter that considers size variants
        if (!is_null($filters['min_price']) || !is_null($filters['max_price'])) {
            $items = $items->where(function($query) use($filters) {
                // For items without size variants, check the base price
                $query->where(function($q) use($filters) {
                    $q->whereDoesntHave('sizeVariants')
                    ->where('price', '>=', $filters['min_price'] ?? 0);
                    
                    if (!is_null($filters['max_price'])) {
                        $q->where('price', '<=', $filters['max_price']);
                    }
                });
                
                // For items with size variants, check if any size variant falls within the price range
                $query->orWhereHas('sizeVariants', function($q) use($filters) {
                    $q->where('stock_quantity', '>', 0)
                    ->where(function($subQ) use($filters) {
                        // Use price_override if set, otherwise use item base price
                        $subQ->whereRaw('COALESCE(price_override, (SELECT price FROM items WHERE items.id = item_variant.item_id)) >= ?', [$filters['min_price'] ?? 0]);
                        
                        if (!is_null($filters['max_price'])) {
                            $subQ->whereRaw('COALESCE(price_override, (SELECT price FROM items WHERE items.id = item_variant.item_id)) <= ?', [$filters['max_price']]);
                        }
                    });
                });
            });
        }

        // NEW: In-stock filter for size variants
        if (!is_null($filters['in_stock']) && $filters['in_stock']) {
            $items = $items->where(function($query) {
                // Items without size variants should have stock > 0
                $query->where(function($q) {
                    $q->whereDoesntHave('sizeVariants')
                    ->where('stock', '>', 0); // Assuming you have a stock column on items table
                })
                // OR items with size variants should have at least one size in stock
                ->orWhereHas('sizeVariants', function($q) {
                    $q->where('stock_quantity', '>', 0);
                });
            });
        }

        // Sorting - UPDATED to consider size variant pricing
        switch ($filters['sort']) {
            case Filter::SORT_LOW_TO_HIGH:
                // Sort by minimum available price (considering size variants)
                $items = $items->select('items.*')
                    ->leftJoin('item_variant as iv_min', function($join) {
                        $join->on('items.id', '=', 'iv_min.item_id')
                            ->where('iv_min.type', 'size')
                            ->where('iv_min.stock_quantity', '>', 0);
                    })
                    ->orderByRaw('COALESCE(MIN(iv_min.price_override), items.price) ASC');
                break;

            case Filter::SORT_HIGH_TO_LOW:
                // Sort by maximum available price (considering size variants)
                $items = $items->select('items.*')
                    ->leftJoin('item_variant as iv_max', function($join) {
                        $join->on('items.id', '=', 'iv_max.item_id')
                            ->where('iv_max.type', 'size')
                            ->where('iv_max.stock_quantity', '>', 0);
                    })
                    ->orderByRaw('COALESCE(MAX(iv_max.price_override), items.price) DESC');
                break;

            case Filter::SORT_A_TO_Z:
                $items = $items->orderBy('name');
                break;

            case Filter::SORT_Z_TO_A:
                $items = $items->orderByDesc('name');
                break;
            
            default:
                $items = $items->orderBy('created_at');
                break;
        }

        if (!$areVariantsShown) {
            $items = $items->whereNull('parent_id');
        }

        // UPDATED: Eager load relationships including size variants
        $items = $items->with([
            'variants' => function($query) use ($filters) {
                if (! $filters['include_inactive']) {
                    $query->visible();
                }

                $query->select('id', 'name', 'parent_id', 'description', 'price', 'saleprice', 'stock', 'is_featured', 'is_active', 'show_rrp', 'is_on_sale', 'selected_shippingid', 'isHideOutOfStock', 'colors')
                    ->with([
                        'sizeVariants' => function($sizeQuery) {
                            $sizeQuery->where('stock_quantity', '>', 0)
                                ->orderBy('display_order')
                                ->select('id', 'item_id', 'value', 'price_override', 'stock_quantity', 'sku');
                        }
                    ]);
            },
            'sizeVariants' => function($query) {
                $query->where('stock_quantity', '>', 0)
                    ->orderBy('display_order')
                    ->select('id', 'item_id', 'value', 'price_override', 'stock_quantity', 'sku');
            },
            'colorVariants' => function($query) {
                $query->where('stock_quantity', '>', 0)
                    ->select('id', 'item_id', 'value', 'stock_quantity');
            },
        ]);

        return new Paginate($items, $filters['max_item_per_page'], $filters['page'], 'items');
    }

    public function retrieveItem(int $id, bool $includeInactive = false): Item
    {
        $item = Item::query()->where('id', $id);

        if (! $includeInactive) {
            $item->visible();
        }

        return $item->firstOrFail()
            ->load([
                'parent:id,name',
                'categories:id,name,parent_id',
                'variants' => function ($query) use ($includeInactive) {
                    if (! $includeInactive) {
                        $query->visible();
                    }
                },
            ])
            ->append(['categoryLineages', 'related']);
    }

    /**
     * @todo Remove coupling to Tag model. Use tag repository or item service instead to find the tag
     */
    public function createItem(
        string $title, 
        string $description, 
        float $price, 
        float $saleprice, 
        int $stock, 
        bool $isFeatured, 
        bool $isRRP, 
        bool $isOnSale, 
        bool $isHideOutOfStock, 
        array $media, 
        array $categoryIds, 
        string $shippingId, 
        array $tags,
        array $sizeVariants = [],
        array $colorVariants = [],
        array $uploadedColorImages = [],
        bool $hasShipping,
        float $shippingCharge,
    ): Item {
        
        $item = new Item();
        $item->name = $title;
        $item->description = $description;
        $item->price = $price;
        $item->saleprice = $saleprice;
        $item->stock = $stock;
        $item->is_featured = $isFeatured;
        $item->show_rrp = $isRRP;
        $item->is_on_sale = $isOnSale;
        $item->selected_shippingid = $shippingId;
        $item->isHideOutOfStock = $isHideOutOfStock;
        $item->has_shipping = $hasShipping;
        $item->shipping_charge = $shippingCharge;
        
        // Legacy colors column cleared; new system uses item_variants table
        $item->colors = []; 

        return DB::transaction(function() use (
            $item, 
            $categoryIds, 
            $tags, 
            $media, 
            $sizeVariants, 
            $colorVariants, 
            $uploadedColorImages,
        ) {
            $item->save();
            
            // 2. Attach Categories
            foreach ($categoryIds as $category) {
                $item->categories()->attach($category);
            }


            // 3. Attach Tags
            foreach ($tags as $tagId) {
                $tag = Tag::findOrFail($tagId);
                $item->tags()->attach($tag);
            }

            // 4. Handle Main Product Media
            foreach ($media as $photo) {
                $itemPhoto = $this->storageService->store($photo);
                $item->media()->save($itemPhoto);
            }

            // 5. Handle Size Variants
            if (!empty($sizeVariants)) {
                $this->updateSizeVariants($item, $sizeVariants);
            }

            // 6. Handle Color Variants (Metadata + Image Mapping)
            if (!empty($colorVariants)) {
                $this->updateColorVariants($item, $colorVariants, $uploadedColorImages);
            }

            // 7. Return Fresh Item with Relationships
            return $item->fresh(['categories', 'tags', 'media', 'itemVariants']);
        });
    }

    /**
     * @todo Check for the multiple photo update thing
     */
    public function duplicateItem(
        int $id,
        ?string $title,
        ?string $description,
        ?float $price,
        ?float $saleprice,
        ?int $stock,
        bool $isFeatured,
        bool $isRRP,
        bool $isOnSale,
        ?array $media,
        ?array $categories,
        ?array $sizeVariants = [],
        ?array $colorVariants = [],
        ?array $uploadedColorImages = [],
        bool $hasShipping,
        ?float $shippingCharge,
    ): Item
    {
        $oldItem = $this->find($id);
        
        $item = $oldItem->replicate();

        if (!is_null($title)) {
            $item->name = $title;
        }
        if (!is_null($description)) {
            $item->description = $description;
        }
        if (!is_null($price)) {
            $item->price = $price;
        }
        if (!is_null($saleprice)) {
            $item->saleprice = $saleprice;
        }
        if (!is_null($stock)) {
            $item->stock = $stock;
        }
        if (!is_null($isFeatured)) {
            $item->is_featured = $isFeatured;
        }
        if (!is_null($isRRP)) {
            $item->show_rrp = $isRRP;
        }
        if (!is_null($isOnSale)) {
            $item->is_on_sale = $isOnSale;
        }
        if (!is_null($hasShipping)) {
            $item->has_shipping = $hasShipping;
        }
        if (!is_null($shippingCharge)) {
            $item->shipping_charge= $shippingCharge;
        }

        return DB::transaction(function() use($oldItem, $item, $categories, $media, $sizeVariants, $colorVariants, $uploadedColorImages) {
            $item->save();

            if (!is_null($categories)) {
                foreach ($categories as $category) {
                    $item->categories()->attach($category);
                }
            } else {
                foreach ($oldItem->categories as $category) {
                    $item->categories()->attach($category);
                }
            }

            // replicate() copies the model columns but not many-to-many links.
            // A true duplicate must retain its tags as well as its categories.
            $item->tags()->sync($oldItem->tags->pluck('id')->all());

            if (!is_null($media)) {
                foreach ($media as $itemMedia) {
                    $itemPhoto = $this->storageService->store($itemMedia);
                    $item->media()->save($itemPhoto);
                }
            } else {
                /**
                 * @todo Make media polymorphic Many to Many so media can be assigned to many items, and other models (in prep for future)
                 * @todo When done with above, make media hashes unique again.
                 */
                foreach ($oldItem->media as $oldMedia) {
                    $itemMedia = $oldMedia->replicate();
                    $item->media()->save($itemMedia);
                }
            }

            if (!empty($sizeVariants)) {
                $this->updateSizeVariants($item, $sizeVariants);
            }

            if (!empty($colorVariants)) {
                $this->updateColorVariants($item, $colorVariants, $uploadedColorImages);
            }

            return $item;
        });
        
    }

    public function addItemVariant(
        int $id,
        ?string $title,
        ?string $description,
        ?float $price,
        ?float $saleprice,
        ?int $stock,
        bool $isFeatured,
        bool $isRRP,
        bool $isOnSale,
        bool $isHideOutOfStock,
        ?array $media,
        ?array $categories,
        ?array $sizeVariants = [],
        ?array $colorVariants = [],
        ?array $uploadedColorImages = [],
        bool $hasShipping,
        ?float $shippingCharge,
    ): Item
    {
        $item = $this->duplicateItem(
            $id,
            $title,
            $description,
            $price,
            $saleprice,
            $stock,
            $isFeatured,
            $isRRP,
            $isOnSale,
            $media,
            $categories,
            $sizeVariants,
            $colorVariants,
            $uploadedColorImages,
            $hasShipping,
            $shippingCharge,
        );

        return DB::transaction(function() use($item, $id){
            $item->parent_id = $id;
            $isSuccess = $item->save();

            if ($isSuccess) {
                return $item;
            } else {
                return null;
            }
        });
    }

    public function updateItem(
        int $id, 
        string $title, 
        string $description, 
        float $price, 
        float $saleprice, 
        int $stock, 
        bool $isFeatured, 
        bool $isRRP, 
        bool $isOnSale, 
        bool $isHideOutOfStock, 
        ?array $media, 
        array $categories, 
        string $shippingId, 
        array $tags,
        array $sizeVariants = [],
        array $colorVariants = [],
        array $uploadedColorImages = [],
        bool $hasShipping,
        ?float $shippingCharge,
    ): bool
    {
        $item = $this->find($id);
        $item->name = $title;
        $item->description = $description;
        $item->price = $price;
        $item->saleprice = $saleprice;
        $item->stock = $stock;
        $item->is_featured = $isFeatured;
        $item->show_rrp = $isRRP;
        $item->is_on_sale = $isOnSale;
        $item->selected_shippingid = $shippingId;
        $item->isHideOutOfStock = $isHideOutOfStock;
        $item->has_shipping = $hasShipping;
        $item->shipping_charge = $shippingCharge;

        return DB::transaction(function() use(
                $item,
                $categories,
                $tags,
                $media,
                $sizeVariants,
                $colorVariants, 
                $uploadedColorImages,
            ) {
            $item->categories()->detach();
            foreach ($categories as $category) {
                $category->items()->attach($item);
            }

            /*
            $item->tags()->detach();
            foreach ($tags as $tagId) {
                $tag = Tag::findOrFail($tagId);
                $item->tags()->attach($tag);
            }
            */

            if (!is_null($media)) {
                $newMedia = array_filter($media, function($mediaItem) {
                    return $mediaItem instanceof UploadedFile;
                });

                $oldMedia = array_filter($media, function($mediaItem) {
                    return !$mediaItem instanceof UploadedFile;
                });

                foreach ($item->media as $existingMedia) {
                    if (
                        $existingMedia->path !== 'media/default/' . self::PLACEHOLDER_IMAGE &&
                        !in_array($existingMedia->hash, $oldMedia)
                    ) {
                        $this->storageService->delete($existingMedia);
                        $existingMedia->delete();
                    }
                }

                foreach ($newMedia as $newPhoto) {
                    $itemPhoto = $this->storageService->store($newPhoto);
                    $item->media()->save($itemPhoto);
                }
            }

            if (!empty($sizeVariants)) {
                $this->updateSizeVariants($item, $sizeVariants);
            }

            if (!empty($colorVariants)) {
                $this->updateColorVariants($item, $colorVariants, $uploadedColorImages);
            }

            return $item->save();
        });
    }

    public function decreaseStocks(int $id, int $amount, ?int $sizeVariantId = null, bool $override = false): bool
    {
        $item = $this->find($id);
        
        if (!$item) {
            throw new ModelNotFoundException("Item with ID {$id} not found.");
        }

        if ($amount > $item->stock) {
            if ($override) {
                $item->stock = 0;
            } else {
                throw new ItemStockCannotBeLowerThanZeroException(
                    "Attempted to decrease item stock below zero. Available: {$item->stock}, Requested: {$amount}"
                );
            }
        } else {
            $item->stock -= $amount;
        }

        $itemSizeVariant = null;
        
        if (!is_null($sizeVariantId)) {
            $itemSizeVariant = ItemVariant::find($sizeVariantId);
            
            if (!$itemSizeVariant) {
                throw new ModelNotFoundException("Item variant with ID {$sizeVariantId} not found.");
            }

            if ($amount > $itemSizeVariant->stock_quantity) {
                if ($override) {
                    $itemSizeVariant->stock_quantity = 0;
                } else {
                    throw new ItemStockCannotBeLowerThanZeroException(
                        "Attempted to decrease variant stock below zero. Available: {$itemSizeVariant->stock_quantity}, Requested: {$amount}"
                    );
                }
            } else {
                $itemSizeVariant->stock_quantity -= $amount;
            }
        }

        return DB::transaction(function() use ($item, $itemSizeVariant) {
            $itemSaved = $item->save();
            $variantSaved = is_null($itemSizeVariant) ? true : $itemSizeVariant->save();
            
            return $itemSaved && $variantSaved;
        });
    }

    /**
     * @todo Delete media
     */
    public function deleteItem(int $id): bool
    {
        $item = $this->find($id);

        return DB::transaction(function() use($item) {
            foreach ($item->categories as $category) {
                $item->categories()->detach($category);
            }

            foreach ($item->tags as $tag) {
                $item->tags()->detach($tag);
            }

            return $item->delete();
        });
    }

    public function discountCodeItems(array $userFilters = []): Paginate
    {
        $items = $this->discountCode->query();
        $filters = array_merge($this->defaultItemListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));
    
        // Search Filter
        if (!is_null($filters['q'])) {
            $items = $items->where(function ($q) use($filters) {
                $q
                    ->where('code', 'like', '%' . $filters['q'] . '%');
            });
        }

        return new Paginate($items, self::MAX_PAGE_ITEMS, $filters['page'], 'items');
    }

    public function listDiscountCode(): Collection
    {
        return $this->discountCode
                        ->orderBy('created_at', 'DESC')
                        ->get();
    }

    public function totalDiscountCode(): int
    {
        return DiscountCode::count();
    }

    public function countItems()
    {
        return Item::count();
    }

    /**
     * Update Size Variants
     */
    protected function updateSizeVariants(Item $item, array $sizeVariants): void
    {
        $existingVariants = $item->itemVariants()
            ->where('type', 'size')
            ->get()
            ->keyBy('id');
        
        $updatedVariantIds = [];
        $sizeVariantType = Variant::getSizeVariant();
        $displayOrder = 0;

        foreach ($sizeVariants as $sizeData) {
            if (empty($sizeData['value'])) continue;

            $updateData = [
                'item_id' => $item->id,
                'variant_id' => $sizeVariantType?->id,
                'type' => 'size',
                'value' => $sizeData['value'],
                'price_override' => isset($sizeData['price_override']) && $sizeData['price_override'] !== '' 
                    ? floatval($sizeData['price_override']) 
                    : null,
                'stock_quantity' => intval($sizeData['stock_quantity'] ?? 0),
                'sku' => $this->generateSizeSku($item, $sizeData),
                'display_order' => $displayOrder++,
                'is_active' => true,
            ];

            if (!empty($sizeData['id']) && $existingVariants->has($sizeData['id'])) {
                $existingVariants->get($sizeData['id'])->update($updateData);
                $updatedVariantIds[] = $sizeData['id'];
            } else {
                $newVariant = ItemVariant::create($updateData);
                $updatedVariantIds[] = $newVariant->id;
            }
        }

        $variantsToDelete = $existingVariants->keys()->diff($updatedVariantIds);
        if ($variantsToDelete->isNotEmpty()) {
            ItemVariant::whereIn('id', $variantsToDelete)->delete();
        }
    }

    /**
     * Update Color Variants (Metadata + Image Uploads)
     */
    protected function updateColorVariants(
        Item $item, 
        array $colorVariants, 
        array $uploadedColorImages = []
    ): void {
        $existingVariants = $item->itemVariants()
            ->where('type', 'color')
            ->get()
            ->keyBy('id');
        
        $updatedVariantIds = [];
        $colorVariantType = Variant::getColorVariant();
        $displayOrder = 0;

        foreach ($colorVariants as $index => $colorData) {
            if (empty($colorData['name'])) continue;

            $hexcode = null;
            if (!empty($colorData['hexcode'])) {
                $hex = strtoupper(trim($colorData['hexcode']));
                if (preg_match('/^#([A-F0-9]{6}|[A-F0-9]{3})$/', $hex)) {
                    $hexcode = strlen($hex) === 4 
                        ? '#' . str_repeat(substr($hex, 1, 1), 2) 
                            . str_repeat(substr($hex, 2, 1), 2) 
                            . str_repeat(substr($hex, 3, 1), 2)
                        : $hex;
                }
            }

            $variantKey = $colorData['id'] ?? ('temp_' . ($colorData['sort_order'] ?? $index));

            $updateData = [
                'item_id' => $item->id,
                'variant_id' => $colorVariantType?->id,
                'type' => 'color',
                'value' => $colorData['name'],
                'hexcode' => $hexcode,
                'use_image' => (bool) ($colorData['use_image'] ?? false),
                'is_active' => (bool) ($colorData['is_active'] ?? true),
                'price_override' => isset($colorData['price_override']) && $colorData['price_override'] !== '' 
                    ? floatval($colorData['price_override']) 
                    : null,
                'stock_quantity' => intval($colorData['stock_quantity'] ?? 0),
                'sku' => $colorData['sku'] ?? $this->generateColorSku($item, $colorData),
                'display_order' => $displayOrder++,
            ];

            $newFile = $uploadedColorImages[$colorData['id'] ?? ''] 
                    ?? $uploadedColorImages[$variantKey] 
                    ?? $uploadedColorImages[$index] 
                    ?? null;
                    
            $hasNewFile = $newFile instanceof UploadedFile;

            if (!empty($colorData['id']) && $existingVariants->has($colorData['id'])) {
                $variant = $existingVariants->get($colorData['id']);
                $variant->update($updateData);
                $updatedVariantIds[] = $variant->id;
                
                if ($hasNewFile) {
                    $variant->media()->delete(); 
                    $this->attachColorImage($variant, [$newFile]);
                }
                
            } else {
                $variant = ItemVariant::create($updateData);
                $updatedVariantIds[] = $variant->id;

                if ($hasNewFile) {
                    $this->attachColorImage($variant, [$newFile]);
                }
            }
        }

        $variantsToDelete = $existingVariants->keys()->diff($updatedVariantIds);
        if ($variantsToDelete->isNotEmpty()) {
            ItemVariant::whereIn('id', $variantsToDelete)->delete();
        }
    }

    /**
     * Helper: Attach uploaded image to color variant
     */
    protected function attachColorImage(ItemVariant $itemVariant, $media): void
    {
        $files = is_array($media) ? $media : [$media];
        try {
            foreach ($files as $photo) {
                if (!$photo instanceof UploadedFile) {
                    continue;
                }

                $colorPhoto = $this->storageService->store($photo);
                $itemVariant->media()->save($colorPhoto);
                
                $itemVariant->update(['image_path' => $colorPhoto->path ?? null]);
            }
        } catch (\Exception $e) {
            \Log::warning('Color image upload failed', [
                'variant_id' => $itemVariant->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate SKU for size variant
     */
    protected function generateSizeSku(Item $item, array $sizeData): string
    {
        // Generate base SKU from item name
        $baseSku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $item->name), 0, 6));
        
        // Use provided SKU suffix or generate from size value
        $sizeSuffix = $sizeData['sku_suffix'] ?? '-' . $sizeData['value'];
        
        return $baseSku . $sizeSuffix;
    }

    /**
     * Helper: Generate SKU for Color Variant
     */
    protected function generateColorSku(Item $item, array $colorData): string
    {
        $baseSku = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $item->name), 0, 6));
        
        if (!empty($colorData['sku_suffix'])) {
            return $baseSku . $colorData['sku_suffix'];
        }
        
        // Fallback: First 3 letters of color name
        $colorSuffix = '-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $colorData['name']), 0, 3));
        return $baseSku . $colorSuffix;
    }

    protected function cacheKey(int $id): string
    {
        return sprintf('item:%d', $id);
    }
}
