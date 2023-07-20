<?php

namespace App\Models;

use App\Repository\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLineItem extends Model
{
    use HasFactory;

    protected const SNIPPET_LENGTH = 120;

    protected $fillable = [
        'item_id',
        'price',
        'quantity',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getThumbnailAttribute()
    {
        $itemMedia = $this->item->media;

        if (count($itemMedia) > 0) {
            $x = env('APP_URL') . '/storage/' . $itemMedia[0]->path;
        } else {
            $x = env('APP_URL') . '/storage/media/default/' . ItemRepositoryInterface::PLACEHOLDER_IMAGE;
        }

        return $x;
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

    public function getValueAttribute()
    {
        return $this->price/100;
    }

    public function getGSTAttribute()
    {
        return $this->value * .1;
    }

    public function getTotalAttribute()
    {
        return ($this->value) * $this->quantity;
    }
}
