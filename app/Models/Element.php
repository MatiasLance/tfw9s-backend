<?php

namespace App\Models;

use App\Modules\Item\Variant;
use App\Models\Variant as VariantModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Element extends Model
{
    use HasFactory;

    protected $hidden = [
        'variant_id',
        'thumbnail_color_value',
        'order',
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

    /**
     * @todo Change image thumbnail test return value
     */
    public function thumbnail()
    {
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
    }
}
