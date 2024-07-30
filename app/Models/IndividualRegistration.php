<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Repository\SeriesRepositoryInterface;

class IndividualRegistration extends Model
{
    use HasFactory;

    protected const SNIPPET_LENGTH = 120;

    public function players() {
        return $this->hasMany(Player::class, 'registration_id');
    }

    public function item()
    {
        return $this->belongsTo(Series::class, 'item_id');
    }

    public function getSubTotalAttribute()
    {
        $subtotal = 0;
        foreach ($this->item as $item) {
            $subtotal += $item->price;
        }
        return $subtotal;
    }

    public function getOrderNumberAttribute()
    {
        return sprintf('%03d', $this->id);
    }

    public function getThumbnailAttribute()
    {
        $item = $this->item;
        if ($item && $item->media->isNotEmpty()) {
            return env('APP_URL') . '/storage/' . $item->media->first()->path;
        } else {
            return env('APP_URL') . '/storage/media/default/' . SeriesRepositoryInterface::PLACEHOLDER_IMAGE;
        }
    }

    public function getSnippetAttribute()
    {
        $description = $this->item->description;
        if (strlen($description) > self::SNIPPET_LENGTH) {
            return substr($description, self::SNIPPET_LENGTH);
        } else {
            return $description;
        }
    }
}
