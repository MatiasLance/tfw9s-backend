<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    use HasFactory;

    protected $table = 'item_variant';
    
    protected $fillable = [
        'item_id',
        'variant_id',
        'value',
        'type',
        'sku',
        'price_override',
        'stock_quantity',
        'display_order',
    ];

    protected $casts = [
        'price_override' => 'integer',
        'stock_quantity' => 'integer',
        'display_order' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Price calculation
    public function getCalculatedPriceAttribute()
    {
        return $this->price_override ?? $this->item->centPrice();
    }

    // Check if in stock
    public function getInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }
}