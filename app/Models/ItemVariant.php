<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    use HasFactory;

    protected $table = 'item_variant';
    protected $fillable = [
        'item_id',
        'color',

    ];

    public function variant()
    {
        return $this->belongsToMany(Variant::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }
}
