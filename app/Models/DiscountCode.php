<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'code',
        'rate',
        'description',
        'amountapplied',
        'usage_limit'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
