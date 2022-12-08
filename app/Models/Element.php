<?php

namespace App\Models;

use App\Modules\Item\Variant;
use App\Models\Variant as VariantModel;
use App\Modules\Variants\Exceptions\ElementInvalidThumbnailException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    use HasFactory;

    protected $hidden = [
        'variant_id',
        'thumbnail_color_value',
        'order',
        'thumbnail_image',
        'thumbnail_type',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'thumbnail'
    ];

    public function getThumbnailAttribute()
    {
        return $this->thumbnail();
    }

    public function variant()
    {
        return $this->belongsTo(VariantModel::class);
    }

    public function thumbnailImage()
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (is_null($this->attributes['thumbnail_type'])) {
                    return null;
                } else {

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
                }
            }
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
