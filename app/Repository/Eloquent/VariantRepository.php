<?php

namespace App\Repository\Eloquent;

use App\Models\Variant;
use App\Models\ItemVariant;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\VariantRepositoryInterface;

class VariantRepository extends BaseRepository implements VariantRepositoryInterface
{
    /**
     * Variant Repository Constructor
     * 
     * @param ItemVariant $itemVariant
     * @param Variant $variant
     */
    public function __construct(ItemVariant $itemVariant, Variant $variant)
    {
        parent::__construct($variant);
        parent::__construct($itemVariant);
    }

    public function retrieveVariant(): ?array
    {
        return Variant::all()->toArray();
    }
    
    public function addVariant($itemId, $colors)
    {
        $existingVariants = ItemVariant::where('item_id', $itemId)
            ->whereIn('color', $colors)
            ->get()
            ->pluck('color')
            ->toArray();

        $newVariants = array_filter($colors, function ($color) use ($existingVariants) {
            return !in_array($color, $existingVariants);
        });

        $data = array_map(function ($color) use ($itemId) {
            return [
                'item_id' => $itemId,
                'color' => $color
            ];
        }, $newVariants);

        if (!empty($data)) {
            ItemVariant::insert($data);
            return true;
        }

        return false;
    }


    public function retrieveItemVariant(int $id): ?array
    {
        return ItemVariant::where('item_id', $id)->get()->toArray();
    }

    public function deleteVariant(int $variantId): bool
    {
        $variant = ItemVariant::find($variantId);
        if ($variant) {
            return $variant->delete();
        }
        return false;
    }

}
