<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterShippingSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        "maxshipping_value",
        "freeshipping_value"
    ];
}
