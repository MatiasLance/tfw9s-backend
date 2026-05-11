<?php

namespace App\Models;

use App\Modules\Currency\Traits\HandlesCurrency;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'isVariant' => 'is_variant',
        'hasVariants' => 'has_variants',
        'hasSizeVariants' => 'has_size_variants',
        'availableSizes' => 'available_sizes',
        'displayPrice' => 'display_price',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'isHideOutOfStock' => 'boolean',
        'colors' => 'array',
    ];

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function getIsVariantAttribute()
    {
        return $this->checkIfVariant();
    }

    public function checkIfVariant()
    {
        return !is_null($this->parent_id);
    }

    /**
     * Related items
     *
     * For some reason Laravel cannot detect accessors that have an uppercase letter on it.
     *
     * @return Attribute
     */
    public function related(): Attribute
    {
        return Attribute::make(
            get: function() {
                return $this->variants;
            }
        );
    }

    public function centPrice(): int
    {
        return $this->getAttributes()['price'];
    }

    public function centSalePrice(): int
    {
        return $this->getAttributes()['saleprice'];
    }

    public function isOnSale(): bool
    {
        return $this->getAttributes()['is_on_sale'];
    }

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
        return $this->morphMany('App\Models\Media', 'imageable');
    }

    public function variants()
    {
        return $this->hasMany(Item::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Item::class, 'parent_id', 'id');
    }

    // =========================================================================
    // NEW METHODS FOR SIZE VARIANTS
    // =========================================================================

    /**
     * Get all item variants (colors and sizes)
     */
    public function itemVariants()
    {
        return $this->hasMany(ItemVariant::class);
    }

    /**
     * Get color variants only
     */
    public function colorVariants()
    {
        return $this->hasMany(ItemVariant::class)->where('type', 'color');
    }

    /**
     * Get size variants only  
     */
    public function sizeVariants()
    {
        return $this->hasMany(ItemVariant::class)->where('type', 'size');
    }

    /**
     * Check if product has size variants
     */
    public function getHasSizeVariantsAttribute()
    {
        return $this->sizeVariants()->exists();
    }

    /**
     * Get available size options with pricing
     */
    public function getAvailableSizesAttribute()
    {
        if (!$this->has_size_variants) {
            return collect();
        }

        return $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->orderBy('display_order')
            ->get()
            ->map(function($variant) {
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

    /**
     * Get minimum price across all size variants
     */
    public function getMinSizePriceAttribute()
    {
        if (!$this->has_size_variants) {
            return $this->centPrice();
        }

        $minPrice = $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->min('price_override');

        return $minPrice ?? $this->centPrice();
    }

    /**
     * Get maximum price across all size variants
     */
    public function getMaxSizePriceAttribute()
    {
        if (!$this->has_size_variants) {
            return $this->centPrice();
        }

        $maxPrice = $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->max('price_override');

        return $maxPrice ?? $this->centPrice();
    }

    /**
     * Get the display price (shows range if sizes have different prices)
     */
    public function getDisplayPriceAttribute()
    {
        if ($this->has_size_variants) {
            $minPrice = $this->min_size_price;
            $maxPrice = $this->max_size_price;
            
            $minPriceDisplay = $this->toPrice($minPrice);
            $maxPriceDisplay = $this->toPrice($maxPrice);
            
            if ($minPrice !== $maxPrice) {
                return "{$minPriceDisplay} - {$maxPriceDisplay}";
            }
            
            return $minPriceDisplay;
        }
        
        return $this->price;
    }

    /**
     * Get the base price without size variations
     * Useful for displaying the starting price
     */
    public function getBasePriceDisplayAttribute()
    {
        return $this->price;
    }

    /**
     * Check if item has any variants (either color or size)
     * This extends your existing has_variants logic
     */
    public function getHasVariantsAttribute()
    {
        return $this->variants()->exists() || 
               $this->itemVariants()->exists();
    }

    /**
     * Get all available variant combinations
     * Useful for complex product pages with both color and size
     */
    public function getVariantCombinationsAttribute()
    {
        $colors = $this->colorVariants()
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'type' => 'color',
                    'value' => $variant->value,
                    'display_name' => $variant->value,
                    'in_stock' => $variant->in_stock,
                ];
            });

        $sizes = $this->sizeVariants()
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'type' => 'size',
                    'value' => $variant->value,
                    'display_name' => $variant->value,
                    'price' => $variant->calculated_price,
                    'price_display' => $this->toPrice($variant->calculated_price),
                    'in_stock' => $variant->in_stock,
                ];
            });

        return [
            'colors' => $colors,
            'sizes' => $sizes,
        ];
    }

    /**
     * Find a specific size variant by size value
     */
    public function findSizeVariant(string $size)
    {
        return $this->sizeVariants()
            ->where('value', $size)
            ->where('stock_quantity', '>', 0)
            ->first();
    }

    /**
     * Check if a specific size is in stock
     */
    public function isSizeInStock(string $size): bool
    {
        $variant = $this->findSizeVariant($size);
        return $variant ? $variant->in_stock : false;
    }

    /**
     * Get price for a specific size
     */
    public function getPriceForSize(string $size)
    {
        $variant = $this->findSizeVariant($size);
        return $variant ? $this->toPrice($variant->calculated_price) : $this->price;
    }

    /**
     * Retrieve the lingeages of the categories associated with this item.
     *
     * @return array
     */
    public function getCategoryLineagesAttribute()
    {
        $lineages = [];
        foreach ($this->categories as $category) {
            $ancestors = $category->ancestors();
            array_push($lineages, $ancestors);
        }
        return $lineages;
    }

    public function getSnippetAttribute() {
        $snippetLength = 160;
        if (isset($this->description) && !is_null($this->description)) {
            $sanitized = $this->sanitize($this->description);
            if (strlen($sanitized) > $snippetLength) {
                return substr($sanitized, 0, $snippetLength) . '...';
            } else {
                return $sanitized;
            }
        }
    }

    /**
     * Remove the html tags and replace hard breaks with spaces.
     *
     * @return string
     */
    protected function sanitize(string $value): string
    {
        $whitespacePattern = "/(<br( )?(\/)?>)|(<\/p>)/mi";
        $sanitized = preg_replace($whitespacePattern, ' ', $value);
        $sanitized = strip_tags($sanitized);
        $sanitized = trim($sanitized);
        $sanitized = html_entity_decode($sanitized);

        return $sanitized;
    }

    /**
     * Get price for a specific size variant ID
     */
    public function getPriceForSizeVariant(?int $sizeVariantId = null): float
    {
        if (!$sizeVariantId) {
            return (float)$this->centPrice();
        }

        $sizeVariant = $this->sizeVariants()->find($sizeVariantId);
        
        if ($sizeVariant) {
            // Use calculated_price if available, otherwise price_override, otherwise base price
            if (isset($sizeVariant->calculated_price) && $sizeVariant->calculated_price > 0) {
                return (float)$sizeVariant->calculated_price * 100;
            } elseif (isset($sizeVariant->price_override) && $sizeVariant->price_override > 0) {
                return (float)$sizeVariant->price_override;
            }
        }

        return $this->centPrice();
    }

    /**
     * Calculate final price with discount and sale logic for a specific size variant
     */
    public function calculateFinalPrice(?int $sizeVariantId = null, bool $hasDiscount = false, float $discountRate = 0): float
    {
        $basePrice = $this->getPriceForSizeVariant($sizeVariantId);
        $salePrice = $this->centSalePrice();
        $onSale = $this->isOnSale();

        if ($onSale && $hasDiscount) {
            $finalPrice = $salePrice * (1 - $discountRate);
        } elseif ($onSale && !$hasDiscount) {
            $finalPrice = $salePrice;
        } elseif (!$onSale && $hasDiscount) {
            $finalPrice = $basePrice * (1 - $discountRate);
        } else {
            $finalPrice = $basePrice;
        }

        return (float)$finalPrice;
    }
}