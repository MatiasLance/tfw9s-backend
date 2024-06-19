<?php

namespace App\Repository\Eloquent;

use App\Models\Variant;
use App\Models\ItemVariant;
use App\Repository\Eloquent\Base\BaseRepository;
use App\Repository\VariantRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
        // Retrieve existing variants for the item that match any of the provided colors.
        $existingVariants = ItemVariant::where('item_id', $itemId)
            ->whereIn('color', $colors)
            ->get()
            ->pluck('color')
            ->toArray();
    
        // Filter out the colors that already exist
        $newVariants = array_filter($colors, function ($color) use ($existingVariants) {
            return !in_array($color, $existingVariants);
        });
    
        // Prepare the data for the new variants
        $data = array_map(function ($color) use ($itemId) {
            return [
                'item_id' => $itemId,
                'color' => $color
            ];
        }, $newVariants);
    
        if (!empty($data)) {
            // Insert the new variants
            ItemVariant::insert($data);
            return ['status' => 'success', 'new' => $newVariants, 'existing' => $existingVariants];
        }
    
        return ['status' => 'exists', 'new' => [], 'existing' => $existingVariants];
    }    

    public function storeVariant(string $name): Variant
    {
        $variant = new Variant();
        $variant->name = $name;

        return DB::transaction(function() use($variant) {
            $variant->save();
            return $variant;
        });
        
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
