<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StateShipping extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'state',
        'shipping_value',
        'insurance_value',
        'registered_value',
        'express_value'
    ];
}
