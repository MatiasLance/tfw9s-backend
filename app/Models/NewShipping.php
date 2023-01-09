<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewShipping extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'country',
        'shipping_value',
        'insurance_value',
        'registered_value',
        'express_value'
    ];
}
