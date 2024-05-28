<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndividualRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'series_id',
        'firstname',
        'lastname',
        'phone_number',
        'email',
        'player_firstname',
        'player_lastname',
        'team_name',
        'dob'
    ];
}