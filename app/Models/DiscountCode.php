<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'rate',
        'description',
        'amountapplied'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'discount_id');
    }
}
