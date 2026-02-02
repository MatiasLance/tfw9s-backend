<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\MockObject\ReturnValueNotConfiguredException;
use Illuminate\Database\Eloquent\SoftDeletes;

class Series extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = [
        'snippet',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'media',
    ];

    public function event()
    {
        return $this->hasMany(Event::class);
    }

    public function team()
    {
        return $this->hasMany(Team::class);
    }
    
    public function teamlimit()
    {
        return $this->hasMany(TeamLimit::class);
    }

    public function media()
    {
        return $this->morphMany('App\Models\Media', 'imageable');
    }

    public function centPrice()
    {
        return $this->getAttributes()['price'];
    }

    public function individualRegistration()
    {
        return $this->hasMany(IndividualRegistration::class);
    }

    public function teamRegistration()
    {
        return $this->hasMany(TeamRegistration::class);
    }

    public function ageGroup()
    {
        return $this->belongsTo(AgeGroup::class, 'agegroup_id');
    }

    public function registrationFormStatus()
    {
        return $this->hasOne(RegistrationFormStatus::class, 'series_id');
    }

    public function getThumbnailAttribute()
    {
        $media = $this->media;

        if (count($media) > 0) {
            $x = env('APP_URL') . '/storage/' . $media[0]->path;
        } else {
            $x = env('APP_URL') . '/storage/media/default/' . 'brand_item_placeholder_thumbnail.png';
        }

        return $x;
    }

        public function getSnippetAttribute() {
        $snippetLength = 160;
        if (isset($this->description) && !is_null($this->description)) {
            $sanitized = $this->sanitize($this->description);
            if (strlen($sanitized) > $snippetLength) {
                return substr($sanitized, 0, $snippetLength) . '...';
            } else {
                return $sanitized;
            }
        }
    }

    /**
     * Remove the html tags and replace hard breaks with spaces.
     *
     * @return string
     */
    protected function sanitize(string $value): string
    {
        $whitespacePattern = "/(<br( )?(\/)?>)|(<\/p>)/mi";
        $sanitized = preg_replace($whitespacePattern, ' ', $value);
        $sanitized = strip_tags($sanitized);
        $sanitized = trim($sanitized);
        $sanitized = html_entity_decode($sanitized);

        return $sanitized;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($series) {
            // Soft delete related models
            $series->event()->delete();
            $series->team()->delete();
            $series->teamlimit()->delete();
        });
    }
}
