<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_firstname',
        'contact_lastname',
        'phone_number',
        'email',
        'player_firstname',
        'player_lastname',
        'team_name',
        'dob',
        'agegroup',
        'description',
    ];

    protected $with = [
        'agegroup',
    ];

    public function registration()
    {
        return $this->belongsTo(IndividualRegistration::class);
    }
    public function agegroup()
    {
        return $this->belongsTo(AgeGroup::class, 'agegroup_id')->withTrashed();
    }

}
