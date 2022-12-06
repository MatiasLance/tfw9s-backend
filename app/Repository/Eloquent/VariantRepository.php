<?php

namespace App\Repository\Eloquent;

use App\Models\Element;
use App\Models\Variant as VariantModel;
use App\Modules\Item\Variant;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\VariantRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VariantRepository extends BaseRepository implements VariantRepositoryInterface
{

    public function __construct(VariantModel $model)
    {
        parent::__construct($model);
    }

    public function list(array $userFilters = []): Collection
    {
        $filters = array_merge($userFilters, self::DEFAULT_LIST_FILTERS);
        $variants = $this->model->query();

        if (!is_null($filters['q']) && $filters['q'] == '') {
            $variants = $variants->where(function($q) use($filters) {
                $q->where('name', 'LIKE', '%' . $filters['q'] . '%');
            });
        }

        $variants = $variants->get();
        return $variants;
    }

    public function retrieveVariant(int $id): VariantModel
    {
        return $this->find($id);
    }

    public function retrieveElement(int $id): Element
    {
        return Element::findOrFail($id);
    }

    public function createVariant(string $name): VariantModel
    {
        $variant = new VariantModel();
        $variant->name = $name;

        $isSuccess = DB::transaction(function() use($variant) {
            return $variant->save();
        });

        if ($isSuccess) {
            return $variant;
        } else {
            return null;
        }
    }

    public function createElements(int $variantId, string $name, ?string $thumbnailType, $thumbnail, ?int $order = null): VariantModel
    {
        $variant = $this->find($variantId);
        
        $element = new Element();
        $element->name = $name;
        $element->thumbnail_type = $thumbnailType;
        
        if ($thumbnailType === Variant::THUMBNAIL_TYPE_COLOR) {
            $element->thumbnail_color_value = $thumbnail;
        } else if ($thumbnailType === Variant::THUMBNAIL_TYPE_IMAGE) {
            // @todo
        } else {
            // @todo throw error
        }

        $element->order = $order;

        $element = DB::transaction(function() use($variant, $element) {
            return $variant
                        ->elements()
                        ->save($element);
        });

        if (!is_null($element)) {
            $variant->refresh();
            return $variant;
        } else {
            return null;
        }
    }

    public function updateVariant(int $id, string $name): bool
    {
        $variant = $this->find($id);
        $variant->name = $name;

        return DB::transaction(function() use($variant) {
            return $variant->save();
        });
    }

    public function updateElements(int $elementId, string $name, ?string $thumbnailType, $thumbnail, ?int $order = null): bool
    {
        $element = Element::findOrFail($elementId);
        $element->name = $name;
        $element->thumbnail_type = $thumbnailType;
        
        if ($thumbnailType === Variant::THUMBNAIL_TYPE_COLOR) {
            $element->thumbnail_color_value = $thumbnail;
        } else if ($thumbnailType === Variant::THUMBNAIL_TYPE_IMAGE) {
            $element->thumbnail_color_value = null;
            // @todo
        } else {
            // @todo throw error
        }

        $element->order = $order;

        return DB::transaction(function() use($element) {
            return $element->save();
        });
    }

    public function deleteVariant(int $variantId): bool
    {
        $variant = $this->find($variantId);
        
        return DB::transaction(function() use($variant) {
            // @todo Delete Item Variants and Elements
            $variant->elements()->delete();
            return $variant->delete();
        });
    }

    public function deleteElements(int $elementId): bool
    {
        $element = Element::findOrFail($elementId);

        return DB::transaction(function() use($element) {
            // @todo Delete item elements
            return $element->delete();
        });
    }
}