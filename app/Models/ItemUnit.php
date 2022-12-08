<?php

namespace App\Models;

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

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Retrieve the elements that this stock is tied to
     * 
     * @return Collection
     */
    public function elements()
    {
        $elementIds = $this->attributes['element_ids'];
        return ItemVariantElement::whereIn('id', $elementIds);
    }
}
