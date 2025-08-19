<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_variant_id', 'quantity'];

    // Quan hệ: Một item thuộc về một giỏ hàng
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Quan hệ: Một item thuộc về một biến thể sản phẩm
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}