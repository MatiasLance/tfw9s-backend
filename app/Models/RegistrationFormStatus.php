<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RegistrationFormStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id',
        'is_show_count_down_timer',
        'timer_mode',
        'opens_at',
        'countdown_unit',
        'countdown_value'
    ];

    protected $appends = ['is_effectively_active'];

    protected $casts = [
        'series_id' => 'integer',
        'is_show_count_down_timer' => 'boolean',
        'opens_at' => 'datetime',
        'countdown_value' => 'integer'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function getIsEffectivelyActiveAttribute(): bool
    {
        if (!$this->is_show_count_down_timer || !$this->opens_at) {
            return false;
        }

        return $this->opens_at->isFuture();
    }
}