<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamRegistration extends Model
{
    use HasFactory;

    // protected $appends = ['teams_with_agegroups'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
      'transaction_id',
      'refund_id',
      'payment_gateway',
      'manager_email',
      'coach_email',
      'item_id',
      'price',
      'refund',
      'is_verified'
    ];

    protected $casts = [
        'is_verified' => 'boolean'
    ];

    public function getOrderNumberAttribute()
    {
        return sprintf('%03d', $this->id);
    }

    public function teams() {
        return $this->hasMany(Team::class, 'registration_id');
    }

    public function item()
    {
        return $this->belongsTo(Series::class, 'item_id');
    }
    // Method to retrieve teams with agegroups
    // public function getTeamsWithAgegroupsAttribute()
    // {
    //     return $this->teams()->with('agegroup')->get();
    // }
}
