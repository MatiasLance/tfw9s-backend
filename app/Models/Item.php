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
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'isHideOutOfStock' => 'boolean'
    ];

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }
    public function saleprice(): Attribute
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
        // todo: if error then return as integer
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
        return $this->hasMany(Media::class);
    }

    public function variants()
    {
        return $this->hasMany(Item::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Item::class, 'parent_id', 'id');
    }

    public function getHasVariantsAttribute()
    {
        return $this->variants()->exists();
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
}
