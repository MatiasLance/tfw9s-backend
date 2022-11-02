<?php

namespace App\Http\Resources;

use App\Modules\Currency\Traits\HandlesCurrency;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    use HandlesCurrency;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->toPrice($this->price),
            'stock' => $this->stock,
            'category' => $this->category->name,
            'tags' => TagResource::collection($this->tags),
        ];
    }
}
