<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->product_variant_id) {
            return [
                'id' => $this->id,
                'type' => 'product',
                'product_name' => $this->productVariant->product->name,
                'variant_info' => [
                    'size' => $this->productVariant->size->name ?? null,
                    'crust' => $this->productVariant->crust->name ?? null,
                ],
                'price' => (float) $this->productVariant->price,
                'quantity' => $this->quantity,
                'subtotal' => (float) $this->productVariant->price * $this->quantity,
                'image_url' => $this->productVariant->product->image_url,
                'product_variant_id' => $this->product_variant_id
            ];
        }

        if ($this->combo_id) {
            return [
                'id' => $this->id,
                'type' => 'combo',
                'combo_name' => $this->combo->name,
                'price' => (float) $this->combo->price,
                'quantity' => $this->quantity,
                'subtotal' => (float) $this->combo->price * $this->quantity,
                'image_url' => $this->combo->image_url,
                'combo_id' => $this->combo_id
            ];
        }

        return [];
    }
}