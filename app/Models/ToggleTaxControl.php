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

    protected $hidden = ['created_at', 'updated_at'];

    public function isToggleControle2(): int
    {
        return $this->getAttributes()['toggleControl2'];
    }
}
