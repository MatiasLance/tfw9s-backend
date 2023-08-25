<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToggleTaxControl extends Model
{
    use HasFactory;

    public $casts = [
        "toggleControl1" => "boolean",
        "toggleControl2" => "boolean"
    ];
}
