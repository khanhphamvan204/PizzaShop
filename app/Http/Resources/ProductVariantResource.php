<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'size_id' => $this->size_id,
            'crust_id' => $this->crust_id,
            'price' => $this->price,
            'size_name' => $this->size?->name,
            'crust_name' => $this->crust?->name,
        ];
    }

}
