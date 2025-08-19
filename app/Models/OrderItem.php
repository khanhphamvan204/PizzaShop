<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_variant_id', 'quantity', 'price'];

    // Quan hệ: Một item thuộc về một đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ: Một item thuộc về một biến thể sản phẩm
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}