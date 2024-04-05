<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Region extends Model
{
    use HasFactory, SoftDeletes;

<<<<<<< Updated upstream
    protected $hidden = [
        'deleted_at',
=======
        /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
>>>>>>> Stashed changes
        'created_at',
        'updated_at',
    ];

<<<<<<< Updated upstream
    public function fields()
    {
        return $this->hasMany(Field::class);
    }
=======
>>>>>>> Stashed changes
}
