<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    public $casts = [
        "addtax_value" => "boolean",
        "includetax_value" => "boolean"
    ];
}
