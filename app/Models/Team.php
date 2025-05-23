<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'media',
        'field',
        'agegroup',
        'series',
        'region'
    ];

    public function series()
    {
        return $this->belongsTo(Series::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function media()
    {
        return $this->morphMany('App\Models\Media', 'imageable');
    }

    public function agegroup()
    {
        return $this->belongsTo(AgeGroup::class, 'agegroup_id')->withTrashed();
    }

    public function registration()
    {
        return $this->belongsTo(TeamRegistration::class);
    }
        public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
    public function listTeams(array $filter)
{
    $query = Team::query()
        ->with(['agegroup', 'series', 'region', 'field', 'media'])
        ->orderBy('name');

    if (!empty($filter['q'])) {
        $query->where('name', 'like', '%'.$filter['q'].'%');
    }

    if (!empty($filter['max_team_per_page'])) {
        return $query->paginate($filter['max_team_per_page']);
    }

    return $query->get();
}
}
