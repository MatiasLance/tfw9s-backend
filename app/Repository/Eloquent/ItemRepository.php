<?php

namespace App\Repository\Eloquent;

use App\Models\Category;
use App\Models\Item;
use App\Models\ItemVariantElement;
use App\Models\Tag;
use App\Modules\Item\Exceptions\ItemStockCannotBeLowerThanZeroException;
use App\Modules\Item\Filter;
use App\Modules\Item\Variant;
use App\Modules\Storage\StorageInterface;
use App\Modules\Utility\Pagination\Paginate;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\ItemRepositoryInterface;
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
    ];

    public function __construct(Item $item, StorageInterface $storageService)
    {
        $this->storageService = $storageService;
        parent::__construct($item);
    }

    public function listItems(array $userFilters = []): Paginate
    {
        $items = $this->model->query();

        $filters = array_merge($this->defaultItemListFilters, array_filter($userFilters, fn ($f) => !is_null($f)));

        // Search Filter
        if (!is_null($filters['q'])) {
            $items = $items->where(function ($q) use($filters) {
                $q
                    ->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
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
                $q->whereIn('id', $filters['tags']);
            });
        }

        // Sorting
        switch ($filters['sort']) {
            case Filter::SORT_LOW_TO_HIGH:
                $items = $items->orderBy('price');
                break;

            case Filter::SORT_HIGH_TO_LOW:
                $items = $items->orderByDesc('price');
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

        return new Paginate($items, self::MAX_PAGE_ITEMS, $filters['page'], 'items');
    }

    public function retrieveItem(int $id): Item
    {
        return $this->model
                        ->query()
                        ->with(['elements'])
                        ->find($id)
                        ->append('categoryLineages');
    }

    /**
     * @todo Remove coupling to Tag model. Use tag repository or item service instead to find the tag
     */
    public function createItem(string $title, string $description, float $price, array $elements, array $media, array $categories, array $tags): Item
    {
        $item = new Item();
        $item->name = $title;
        $item->description = $description;
        $item->price = $price;

        return DB::transaction(function() use($item, $categories, $tags, $media, $elements) {
            $item->save();
            
            foreach ($categories as $category) {
                $item->categories()->attach($category);
            }
            
            foreach ($tags as $tagId) {
                $tag = Tag::findOrFail($tagId);
                $item->tags()->attach($tag);
            }

            foreach ($media as $photo) {
                $itemPhoto = $this->storageService->store($photo);
                $item->media()->save($itemPhoto);
            }

            foreach ($elements as $element) {
                $itemElement = new ItemVariantElement();
                $itemElement->element_id = $element['element_id'];
                $itemElement->stock = $element['stock'] ?? 0;
                $itemElement->price = $element['price'] ?? null;
                $itemElement->order = $element['order'] ?? null;
                $itemElement->thumbnail_type = $element['thumbnail_type'];
                
                if ($element['thumbnail_type'] === Variant::THUMBNAIL_TYPE_COLOR) {
                    $itemElement->thumbnail_color_value = $element['thumbnail'];
                } else if ($element['thumbnail_type'] === Variant::THUMBNAIL_TYPE_IMAGE) {
                    $elementThumbnail = $this->storageService->store($element['thumbnail']);
                    $item->elements()->save($itemElement); // Need the element to be saved first

                    $itemElement->thumbnailImage()->save($elementThumbnail);
                }

                if ($element['thumbnail_type'] !== Variant::THUMBNAIL_TYPE_IMAGE) {
                    $item->elements()->save($itemElement);
                }
            }

            return $this->retrieveItem($item->id);
        });
    }

    /**
     * @todo Check for the multiple photo update thing
     */
    public function duplicateItem(int $id, ?string $title, ?string $description, ?float $price, ?array $elements, ?array $media, ?array $categories, ?array $tags): Item
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

        return DB::transaction(function() use($oldItem, $item, $categories, $tags, $media, $elements) {
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

            if (!is_null($tags)) {
                foreach ($tags as $tagId) {
                    $tag = Tag::findOrFail($tagId);
                    $item->tags()->attach($tag);
                }
            } else {
                foreach ($oldItem->tags as $tag) {
                    $item->tags()->attach($tag);
                }
            }

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

            if (!is_null($elements) && count($elements) > 0) {
                foreach ($elements as $element) {
                    $itemElement = new ItemVariantElement();
                    $itemElement->element_id = $element['element_id'];
                    $itemElement->stock = $element['stock'] ?? 0;
                    $itemElement->price = $element['price'] ?? null;
                    $itemElement->order = $element['order'] ?? null;
                    $itemElement->thumbnail_type = $element['thumbnail_type'];
                    
                    if ($element['thumbnail_type'] === Variant::THUMBNAIL_TYPE_COLOR) {
                        $itemElement->thumbnail_color_value = $element['thumbnail'];
                    } else if ($element['thumbnail_type'] === Variant::THUMBNAIL_TYPE_IMAGE) {
                        $elementThumbnail = $this->storageService->store($element['thumbnail']);
                        $item->elements()->save($itemElement); // Need the element to be saved first
    
                        $itemElement->thumbnailImage()->save($elementThumbnail);
                    }
    
                    if ($element['thumbnail_type'] !== Variant::THUMBNAIL_TYPE_IMAGE) {
                        $item->elements()->save($itemElement);
                    }
                }
            } else {
                foreach ($oldItem->elements as $element) {
                    $itemElement = $element->replicate();
                    $item->elements()->save($itemElement);
                }
            }

            return $item;
        });
        
    }

    public function updateItem(int $id, string $title, string $description, float $price, array $elements, ?array $media, array $categories, array $tags): bool
    {
        $item = $this->find($id);
        $item->name = $title;
        $item->description = $description;
        $item->price = $price;

        return DB::transaction(function() use($item, $categories, $tags, $media, $elements) {
            $item->categories()->detach();
            foreach ($categories as $category) {
                $category->items()->attach($item);
            }

            $item->tags()->detach();
            foreach ($tags as $tagId) {
                $tag = Tag::findOrFail($tagId);
                $item->tags()->attach($tag);
            }

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

            if (!is_null($elements) || count($elements) > 0) {
                
                foreach ($item->elements as $itemElement) {
                    if ($itemElement->thumbnail_type === Variant::THUMBNAIL_TYPE_IMAGE) {
                        $itemElementMedia = $itemElement->thumbnailImage;
                        $this->storageService->delete($itemElementMedia);
                        $itemElement->thumbnailImage()->delete();
                    }
                }

                foreach ($elements as $element) {
                    $itemElement = new ItemVariantElement();
                    $itemElement->element_id = $element['element_id'];
                    $itemElement->stock = $element['stock'] ?? 0;
                    $itemElement->price = $element['price'] ?? null;
                    $itemElement->order = $element['order'] ?? null;
                    $itemElement->thumbnail_type = $element['thumbnail_type'];
                    
                    if ($element['thumbnail_type'] === Variant::THUMBNAIL_TYPE_COLOR) {
                        $itemElement->thumbnail_color_value = $element['thumbnail'];
                    } else if ($element['thumbnail_type'] === Variant::THUMBNAIL_TYPE_IMAGE) {
                        $elementThumbnail = $this->storageService->store($element['thumbnail']);
                        $item->elements()->save($itemElement); // Need the element to be saved first
    
                        $itemElement->thumbnailImage()->save($elementThumbnail);
                    }
    
                    if ($element['thumbnail_type'] !== Variant::THUMBNAIL_TYPE_IMAGE) {
                        $item->elements()->save($itemElement);
                    }
                }
            }

            return $item->save();
        });
    }

    public function decreaseStocks(int $id, int $amount, bool $override = false): bool
    {
        $item = $this->find($id);

        if ($amount > $item->stock) {

            if ($override) {
                $item->stock = 0;
            }

            throw new ItemStockCannotBeLowerThanZeroException('Attempted to decease the stocks below zero');
        } else {
            $item->stock -= $amount;
        }

        return true;
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

            $item->elements()->delete();

            return $item->delete();
        });
    }
}