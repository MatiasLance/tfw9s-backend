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
        'snippet'
    ];

    public function price(): Attribute
    {
        return Attribute::make(
            get: fn ($val) => $this->toPrice($val),
            set: fn ($val) => $this->toCent($val),
        );
    }

    public function centPrice(): int
    {
        return $this->getAttributes()['price'];
    }

    public function elements()
    {
        return $this->hasMany(ItemVariantElement::class);
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
        $sanitized = $this->sanitize($this->description);
        if (strlen($sanitized) > $snippetLength) {
            return substr($sanitized, 0, $snippetLength) . '...';
        } else {
            return $sanitized;
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

        return $sanitized;
    }
}
