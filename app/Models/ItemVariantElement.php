<?php

namespace App\Models;

use App\Modules\Item\Variant;
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

    /**
     * @todo Change image thumbnail test return value
     */
    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: function() {
                if (!is_null($this->thumbnail_type)) {
                    if (is_null($this->thumbnail_type)) {
                        return null;
                    } else {
                        
                        if ($this->thumbnail_type === Variant::THUMBNAIL_TYPE_IMAGE) {
                            return [
                                'hash' => 'test',
                                'path' => 'test',
                            ];
                        } else if ($this->thumbnail_type === Variant::THUMBNAIL_TYPE_COLOR) {
                            return [
                                'value' => $this->thumbnail_color_value,
                            ];
                        }
            
                    }
                } else {
                    return $this->element->thumbnail();
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
}
