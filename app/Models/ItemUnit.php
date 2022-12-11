<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ItemUnit extends Model
{
    use HasFactory;

    protected $hidden = [
        'item_id',
        'element_ids',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'element_ids' => 'array'
    ];

    protected $appends = [
        'elements'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Retrieve the elements that this stock is tied to
     * 
     * @return Attribute
     */
    public function elements(): Attribute
    {
        return Attribute::make(
            get: function() {
                return ItemVariantElement::whereIn('id', $this->element_ids);
            }
        );
    }
}
