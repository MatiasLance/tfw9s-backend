<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ItemVariant;

class ItemVariantController extends Controller
{
     public function index(Item $item)
    {
        return response()->json($item->variants);
    }

    public function store(Item $item, Request $request)
    {
        $validated = $request->validate([
            'size' => 'required|string|in:Small,Medium,Large,Extra Large,2XL,3XL',
            'price_adjustment' => 'required|integer',
            'stock' => 'required|integer|min:0'
        ]);

        $variant = $item->variants()->create($validated);

        return response()->json($variant);
    }

    public function update(Item $item, ItemVariant $variant, Request $request)
    {
        // Ensure the variant belongs to the item
        if ($variant->item_id !== $item->id) {
            abort(404);
        }

        $validated = $request->validate([
            'price_adjustment' => 'integer',
            'stock' => 'integer|min:0',
            'is_active' => 'boolean'
        ]);

        $variant->update($validated);

        return response()->json($variant);
    }

    public function destroy(Item $item, ItemVariant $variant)
    {
        if ($variant->item_id !== $item->id) {
            abort(404);
        }

        $variant->delete();

        return response()->json(['message' => 'Variant deleted']);
    }
}
