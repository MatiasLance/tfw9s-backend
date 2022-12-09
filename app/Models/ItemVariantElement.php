<?php

namespace App\Models;

use App\Modules\Item\Variant;
use App\Modules\Variants\Exceptions\ElementInvalidThumbnailException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVariantElement extends Model
{
    use HasFactory;

    protected $hidden = [
        'item_id',
        'element_id',
        'thumbnail_color_value',
        'order',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'element',
    ];

    protected $appends = [
        'name',
        'thumbnail',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function element()
    {
        return $this->belongsTo(Element::class);
    }

    public function getNameAttribute()
    {
        return $this->element->name;
    }

    protected function thumbnailType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => !is_null($value) ? $value : $this->element->thumbnail_type,
        );
    }

    public function thumbnailImage()
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    protected function thumbnailColorValue(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (!is_null($value)) {
                    return strtolower($value);
                }
            },
        );
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (
                    !is_null($this->attributes['thumbnail_type']) &&
                    !is_null($this->thumbnailImage)
                ) {
                    if ($this->attributes['thumbnail_type'] === Variant::THUMBNAIL_TYPE_IMAGE) {
                        if (!is_null($this->thumbnailImage) && !is_null($this->thumbnailImage->path)) {
                            return [
                                'type' => $this->attributes['thumbnail_type'],
                                'value' => $this->thumbnailImage->path,
                            ];
                        }
        
                        throw new ElementInvalidThumbnailException('Thumbnail type was set to image but had no image associated as thumbnail');
                    } else if ($this->attributes['thumbnail_type'] === Variant::THUMBNAIL_TYPE_COLOR) {
                        return [
                            'type' => $this->attributes['thumbnail_type'],
                            'value' => $this->thumbnail_color_value,
                        ];
                    }
                } else {
                    return $this->element->thumbnail;
                }
            }
        );
    }

    protected function order(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => !is_null($value) ? $value : $this->element->order,
        );
    }

    public function toArray()
    {
        $array = parent::toArray();

        // Remove the duplicate thumbnail_image property
        unset($array['thumbnail_image']);
        return $array;
    }
}
