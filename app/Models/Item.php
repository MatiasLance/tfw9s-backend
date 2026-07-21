<?php

namespace App\Models;

use App\Modules\Currency\Traits\HandlesCurrency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HandlesCurrency;

    protected $hidden = [
        'parent_id',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'categories',
        'tags',
        'media',
    ];

    protected $appends = [
        'snippet',
        'isVariant',
        'hasVariants',
        'hasSizeVariants',
        'hasColorVariants',
        'availableSizes',
        'availableColors',
        'displayPrice',
        // Optional: Auto-include API helpers in JSON responses
        // 'colorVariantsForApi',
        // 'sizeVariantsForApi',
        // 'allVariantsForApi',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_on_sale' => 'boolean',
        'is_active' => 'boolean',
        'isHideOutOfStock' => 'boolean',
        'colors' => 'array',
        'show_rrp' => 'boolean',
        'has_shipping' => 'boolean'
    ];

    /**
     * Limit a query to products that may be shown or purchased publicly.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('is_active'), true);
    }

    // =========================================================================
    // PRICE ATTRIBUTES (Cents in DB → Dollars via Accessor)
    // =========================================================================

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $val === null ? 0 : $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function saleprice(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $val === null ? 0 : $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function centPrice(): int
    {
        return (int) $this->getAttributes()['price'];
    }

    public function centSalePrice(): int
    {
        return (int) $this->getAttributes()['saleprice'];
    }

    public function isOnSale(): bool
    {
        return (bool) $this->getAttributes()['is_on_sale'];
    }

    // =========================================================================
    // VARIANT TYPE CHECKS
    // =========================================================================

    public function getIsVariantAttribute()
    {
        return $this->checkIfVariant();
    }

    public function checkIfVariant()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Related items (legacy variant system via parent_id)
     */
    public function related(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->variants,
        );
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'item_categories');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'item_tags');
    }

    public function media()
    {
        return $this->morphMany(\App\Models\Media::class, 'imageable');
    }

    // Legacy variant system (parent_id hierarchy)
    public function variants()
    {
        return $this->hasMany(Item::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Item::class, 'parent_id', 'id');
    }

    // New variant system (item_variants table for size/color)
    public function itemVariants()
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function colorVariants()
    {
        return $this->hasMany(ItemVariant::class)->where('type', 'color');
    }

    public function sizeVariants()
    {
        return $this->hasMany(ItemVariant::class)->where('type', 'size');
    }

    // =========================================================================
    // SIZE VARIANT ACCESSORS
    // =========================================================================

    public function getHasSizeVariantsAttribute()
    {
        return $this->sizeVariants()->exists();
    }

    public function getAvailableSizesAttribute()
    {
        if (!$this->has_size_variants) {
            return collect();
        }

        return $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'size' => $variant->value,
                    'price' => $variant->calculated_price,
                    'price_display' => $this->toPrice($variant->calculated_price),
                    'sku' => $variant->sku,
                    'in_stock' => $variant->in_stock,
                    'stock_quantity' => $variant->stock_quantity,
                ];
            });
    }

    public function getMinSizePriceAttribute()
    {
        if (!$this->has_size_variants) {
            return $this->centPrice();
        }

        $minPrice = $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->min('price_override');

        return $minPrice !== null ? (int) $minPrice : $this->centPrice();
    }

    public function getMaxSizePriceAttribute()
    {
        if (!$this->has_size_variants) {
            return $this->centPrice();
        }

        $maxPrice = $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->max('price_override');

        return $maxPrice !== null ? (int) $maxPrice : $this->centPrice();
    }

    public function getBasePriceDisplayAttribute()
    {
        return $this->price;
    }

    // =========================================================================
    // COLOR VARIANT ACCESSORS
    // =========================================================================

    public function getHasColorVariantsAttribute()
    {
        return $this->colorVariants()->exists();
    }

    public function getAvailableColorsAttribute()
    {
        if (!$this->has_color_variants) {
            return collect();
        }

        return $this->colorVariants()
            ->where('stock_quantity', '>=', 0)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->value,
                    'hexcode' => $variant->hexcode,
                    'image_url' => $variant->image_url,
                    'use_image' => (bool) $variant->use_image,
                    'preview' => $variant->preview,
                    'sku' => $variant->sku,
                    'price' => $variant->calculated_price,
                    'price_display' => $this->toPrice($variant->calculated_price),
                    'in_stock' => $variant->in_stock,
                    'stock_quantity' => $variant->stock_quantity,
                    'is_active' => (bool) $variant->is_active,
                    'sort_order' => $variant->display_order,
                ];
            });
    }

    public function getMinColorPriceAttribute()
    {
        if (!$this->has_color_variants) {
            return $this->centPrice();
        }

        $minPrice = $this->colorVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->min('price_override');

        return $minPrice !== null ? (int) $minPrice : $this->centPrice();
    }

    public function getMaxColorPriceAttribute()
    {
        if (!$this->has_color_variants) {
            return $this->centPrice();
        }

        $maxPrice = $this->colorVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->max('price_override');

        return $maxPrice !== null ? (int) $maxPrice : $this->centPrice();
    }

    public function findColorVariant(string $identifier)
    {
        // Try matching by name first
        $variant = $this->colorVariants()
            ->where('value', $identifier)
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->first();

        // Fallback: match by hexcode
        if (!$variant && str_starts_with($identifier, '#')) {
            $variant = $this->colorVariants()
                ->where('hexcode', strtoupper($identifier))
                ->where('stock_quantity', '>', 0)
                ->where('is_active', true)
                ->first();
        }

        return $variant;
    }

    public function isColorInStock(string $identifier): bool
    {
        $variant = $this->findColorVariant($identifier);
        return $variant ? $variant->in_stock : false;
    }

    public function getPriceForColor(string $identifier)
    {
        $variant = $this->findColorVariant($identifier);
        return $variant ? $this->toPrice($variant->calculated_price) : $this->price;
    }

    // =========================================================================
    // PRICE CALCULATION HELPERS (Consistent: Return Dollars)
    // =========================================================================

    public function getPriceForSizeVariant(?int $sizeVariantId = null): float
    {
        if (!$sizeVariantId) {
            return (float) $this->price;
        }

        $sizeVariant = $this->sizeVariants()->find($sizeVariantId);
        
        if ($sizeVariant && $sizeVariant->calculated_price > 0) {
            return (float) $sizeVariant->calculated_price;
        }

        return (float) $this->price;
    }

    public function getPriceForColorVariant(?int $colorVariantId = null): float
    {
        if (!$colorVariantId) {
            return (float) $this->price;
        }

        $colorVariant = $this->colorVariants()->find($colorVariantId);
        
        if ($colorVariant && $colorVariant->calculated_price > 0) {
            return (float) $colorVariant->calculated_price;
        }

        return (float) $this->price;
    }

    public function calculateFinalPrice(
        ?int $variantId = null, 
        bool $hasDiscount = false, 
        float $discountRate = 0
    ): float {
        $basePrice = $variantId 
            ? ($this->getPriceForSizeVariant($variantId) ?: $this->getPriceForColorVariant($variantId))
            : (float) $this->price;
            
        $salePrice = (float) $this->saleprice;
        $onSale = $this->isOnSale();

        if ($onSale && $hasDiscount) {
            return $salePrice * (1 - $discountRate);
        } elseif ($onSale) {
            return $salePrice;
        } elseif ($hasDiscount) {
            return $basePrice * (1 - $discountRate);
        }
        
        return $basePrice;
    }

    public function getDisplayPriceAttribute()
    {
        $prices = [];

        // Collect size variant prices (stored as cents in DB)
        if ($this->has_size_variants) {
            $sizePrices = $this->sizeVariants()
                ->where('stock_quantity', '>', 0)
                ->where('is_active', true)
                ->pluck('price_override')
                ->filter()
                ->map(fn($p) => (int) $p);
            
            if ($sizePrices->isNotEmpty()) {
                $prices = array_merge($prices, $sizePrices->toArray());
            } else {
                $prices[] = $this->centPrice();
            }
        }
        
        // Collect color variant prices
        if ($this->has_color_variants) {
            $colorPrices = $this->colorVariants()
                ->where('stock_quantity', '>', 0)
                ->where('is_active', true)
                ->pluck('price_override')
                ->filter()
                ->map(fn($p) => (int) $p);
            
            if ($colorPrices->isNotEmpty()) {
                $prices = array_merge($prices, $colorPrices->toArray());
            } elseif (empty($prices)) {
                $prices[] = $this->centPrice();
            }
        }

        // Fallback to base price
        if (empty($prices)) {
            $prices[] = $this->centPrice();
        }

        $minPrice = min($prices);
        $maxPrice = max($prices);
        
        $minDisplay = $this->toPrice($minPrice);
        $maxDisplay = $this->toPrice($maxPrice);
        
        if ($minPrice !== $maxPrice) {
            return "{$minDisplay} - {$maxDisplay}";
        }
        
        return $minDisplay;
    }

    // =========================================================================
    // VARIANT COMBINATIONS (Frontend-Ready)
    // =========================================================================

    public function getHasVariantsAttribute()
    {
        return $this->variants()->exists() || $this->itemVariants()->exists();
    }

    public function getVariantCombinationsAttribute()
    {
        $colors = $this->colorVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'type' => 'color',
                    'value' => $variant->value,
                    'name' => $variant->value,
                    'hexcode' => $variant->hexcode,
                    'image_url' => $variant->image_url,
                    'use_image' => (bool) $variant->use_image,
                    'preview' => $variant->preview,
                    'display_name' => $variant->value,
                    'price' => $variant->calculated_price,
                    'price_display' => $this->toPrice($variant->calculated_price),
                    'sku' => $variant->sku,
                    'in_stock' => $variant->in_stock,
                    'stock_quantity' => $variant->stock_quantity,
                ];
            });

        $sizes = $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'type' => 'size',
                    'value' => $variant->value,
                    'display_name' => $variant->value,
                    'price' => $variant->calculated_price,
                    'price_display' => $this->toPrice($variant->calculated_price),
                    'sku' => $variant->sku,
                    'in_stock' => $variant->in_stock,
                    'stock_quantity' => $variant->stock_quantity,
                ];
            });

        return [
            'colors' => $colors,
            'sizes' => $sizes,
        ];
    }

    public function findSizeVariant(string $size)
    {
        return $this->sizeVariants()
            ->where('value', $size)
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->first();
    }

    public function isSizeInStock(string $size): bool
    {
        $variant = $this->findSizeVariant($size);
        return $variant ? $variant->in_stock : false;
    }

    public function getPriceForSize(string $size)
    {
        $variant = $this->findSizeVariant($size);
        return $variant ? $this->toPrice($variant->calculated_price) : $this->price;
    }

    // =========================================================================
    // API RESPONSE HELPERS
    // =========================================================================

    public function getColorVariantsForApiAttribute()
    {
        return $this->colorVariants()
            ->orderBy('display_order')
            ->get()
            ->map->toApiArray();
    }

    public function getSizeVariantsForApiAttribute()
    {
        return $this->sizeVariants()
            ->orderBy('display_order')
            ->get()
            ->map->toApiArray();
    }

    public function getAllVariantsForApiAttribute()
    {
        return [
            'colors' => $this->colorVariantsForApi,
            'sizes' => $this->sizeVariantsForApi,
            'has_colors' => $this->has_color_variants,
            'has_sizes' => $this->has_size_variants,
            'price_range' => $this->display_price,
        ];
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    public function getCategoryLineagesAttribute()
    {
        $lineages = [];
        foreach ($this->categories as $category) {
            $ancestors = $category->ancestors();
            array_push($lineages, $ancestors);
        }
        return $lineages;
    }

    public function getSnippetAttribute()
    {
        $snippetLength = 160;
        if (isset($this->description) && !is_null($this->description)) {
            $sanitized = $this->sanitize($this->description);
            if (strlen($sanitized) > $snippetLength) {
                return substr($sanitized, 0, $snippetLength) . '...';
            }
            return $sanitized;
        }
        return '';
    }

    protected function sanitize(string $value): string
    {
        $whitespacePattern = "/(<br( )?(\/)?>)|(<\/p>)/mi";
        $sanitized = preg_replace($whitespacePattern, ' ', $value);
        $sanitized = strip_tags($sanitized);
        $sanitized = trim($sanitized);
        $sanitized = html_entity_decode($sanitized);
        return $sanitized;
    }

    // =========================================================================
    // MODEL EVENTS & CACHE INVALIDATION
    // =========================================================================

    protected static function booted()
    {
        static::deleting(function ($item) {
            if (!$item->isForceDeleting()) {
                $item->itemVariants()->delete();
            }
        });
    }
}
