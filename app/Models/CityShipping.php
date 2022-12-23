<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityShipping extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'city',
        'shipping_value',
        'insurance_value',
        'registered_value',
        'express_value'
    ];
}
