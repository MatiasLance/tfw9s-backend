<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Players extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'contact_firstname',
        'contact_lastname',
        'phone_number',
        'email',
        'player_firstname',
        'player_lastname',
        'team_name',
        'dob',
        'agegroup',
        'description'
    ];
}
