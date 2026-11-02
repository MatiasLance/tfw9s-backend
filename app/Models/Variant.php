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

    /**
     * Get (or create) the single colour variant definition.
     *
     * Only the type is used to locate an existing row. Matching on the JSON
     * "values" column is unreliable in MySQL, which caused a new duplicate
     * definition to be created every time this method was called.
     */
    public static function getColorVariant()
    {
        return static::firstOrCreate(
            ['type' => 'color'],
            [
                'name' => 'Color',
                'values' => ['Red', 'Blue', 'Green', 'Black', 'White'],
            ]
        );
    }

    /**
     * Get (or create) the single size variant definition.
     */
    public static function getSizeVariant()
    {
        return static::firstOrCreate(
            ['type' => 'size'],
            [
                'name' => 'Size',
                'values' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
            ]
        );
    }
}
