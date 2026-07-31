<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'region',
        'manager',
        'agegroup',
        'series',
        'eventmatch',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class)->withTrashed();
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }

    public function agegroup()
    {
        return $this->belongsTo(AgeGroup::class)->withTrashed();
    }

    public function series()
    {
        return $this->belongsTo(Series::class)->withTrashed();
    }

    public function eventmatch()
    {
        return $this->hasMany(EventMatch::class);
    }

    public function teamposition()
    {
        return $this->hasMany(TeamPosition::class);
    }

    public function getTimeAttribute(?string $value): ?string
    {
        if (! preg_match('/^(\d{1,2}):([0-5]\d)(?::[0-5]\d)?$/', (string) $value, $matches)) {
            return $value;
        }

        $hours = (int) $matches[1];

        if ($hours > 23) {
            return $value;
        }

        return sprintf('%02d:%s', $hours, $matches[2]);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($event) {
            // Soft delete related models
            $event->eventmatch()->delete();
            $event->teamposition()->delete();
        });
    }
}
