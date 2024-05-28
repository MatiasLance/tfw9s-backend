<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'coach_name',
        'coach_email',
        'coach_number',
        'team_name',
        'manager_name',
        'manager_email',
        'manager_number',
        'age_group'
    ];
}