<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ItemVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'item_variant';

    protected $fillable = [
        'item_id',
        'variant_id',
        'type',
        'value',
        'hexcode',
        'image_path',
        'use_image',
        'sku',
        'price_override',
        'stock_quantity',
        'display_order',
        'is_active',
    ];

    protected $with = [
        'media',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'stock_quantity' => 'integer',
        'display_order' => 'integer',
        'use_image' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the variant type (Size/Color definition)
     */
    public function variantType()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    // Accessor: Get full image URL for color swatch
    public function getImageUrlAttribute()
    {
        if (!$this->image_path) {
            return null;
        }
        
        // Handle both relative paths and full URLs
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }
        
        return asset('storage/' . ltrim($this->image_path, '/'));
    }

    // Accessor: Get preview data for frontend (hexcode OR image URL)
    public function getPreviewAttribute()
    {
        if ($this->type !== 'color') {
            return null;
        }
        
        return [
            'type' => $this->use_image && $this->image_url ? 'image' : 'hex',
            'value' => $this->use_image && $this->image_url 
                ? $this->image_url 
                : $this->hexcode
        ];
    }

    // Accessor: Get variant label for display
    public function getLabelAttribute()
    {
        return $this->value; // "M" for size, "Forest Green" for color
    }

    // Price calculation (updated for decimal casting)
    public function getCalculatedPriceAttribute()
    {
        if ($this->price_override !== null) {
            return (float) $this->price_override;
        }
        
        // Fallback to parent item price (assuming centPrice() returns cents)
        return $this->item?->centPrice() / 100;
    }

    // Stock check
    public function getInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    // Scope: Filter by variant type
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Scope: Only color variants
    public function scopeColors($query)
    {
        return $query->where('type', 'color');
    }

    // Scope: Only size variants
    public function scopeSizes($query)
    {
        return $query->where('type', 'size');
    }

    // Scope: Active variants only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Order by display order
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('value');
    }

    // Relationships
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function media()
    {
        return $this->morphMany(\App\Models\Media::class, 'imageable');
    }

    // Helper: Check if this is a color variant
    public function isColor(): bool
    {
        return $this->type === 'color';
    }

    // Helper: Check if this is a size variant
    public function isSize(): bool
    {
        return $this->type === 'size';
    }

    // Helper: Get API-ready array for frontend
    public function toApiArray()
    {
        $base = [
            'id' => $this->id,
            'type' => $this->type,
            'value' => $this->value,
            'label' => $this->label,
            'sku' => $this->sku,
            'price' => $this->calculated_price,
            'price_override' => $this->price_override,
            'stock_quantity' => $this->stock_quantity,
            'in_stock' => $this->in_stock,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];

        if ($this->isColor()) {
            $base = array_merge($base, [
                'hexcode' => $this->hexcode,
                'image_url' => $this->image_url,
                'use_image' => $this->use_image,
                'preview' => $this->preview,
            ]);
        }

        if ($this->media->isNotEmpty()) {
            $base['media'] = $this->media->map(fn($m) => [
                'id' => $m->id,
                'url' => $m->getUrlAttribute(),
                'path' => $m->path,
            ]);
        }

        return $base;
    }

    
    protected static function booted()
    {
        static::saved(function (ItemVariant $variant) {
            Cache::forget("item:{$variant->item_id}");
        });

        static::deleted(function (ItemVariant $variant) {
            Cache::forget("item:{$variant->item_id}");
        });
    }
}