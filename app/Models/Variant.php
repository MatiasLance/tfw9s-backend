<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    public function itemVariants()
    {
        return $this->hasMany(ItemVariant::class);
    }

    public static function getColorVariant()
    {
        return static::where('type', 'color')->firstOrCreate([
            'name' => 'Color',
            'type' => 'color',
            'values' => ['Red', 'Blue', 'Green', 'Black', 'White']
        ]);
    }

    public static function getSizeVariant()
    {
        return static::where('type', 'size')->firstOrCreate([
            'name' => 'Size',
            'type' => 'size', 
            'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL']
        ]);
    }
}