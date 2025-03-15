<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'price'       => $this->price,
            'discount'    => $this->discount,
            'stock'       => $this->stock,
            'status'      => $this->status,
            'images'      => $this->images->select('id', 'image_url'),
            'description' => $this->description,
        ];
    }
}
