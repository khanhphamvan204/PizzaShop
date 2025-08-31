<?php

namespace App\Http\Resources;

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
        // $this ở đây là một đối tượng ProductVariant
        return [
            'id' => $this->product->id,
            'variant_id' => $this->id,
            'name' => $this->product->name,
            'description' => $this->product->description,
            'image_url' => $this->product->image_url,
            'category' => $this->whenLoaded('product', fn() => $this->product->category->name ?? 'N/A'),
            'size' => $this->whenLoaded('size', fn() => $this->size->name ?? null),
            'crust' => $this->whenLoaded('crust', fn() => $this->crust->name ?? null),
            'price' => $this->price,
            'stock' => $this->stock,
            'type' => 'product'
        ];
    }
}