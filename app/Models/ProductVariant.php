<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'size_id', 'crust_id', 'price', 'stock'];

    // Quan hệ: Một biến thể thuộc về một sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Quan hệ: Một biến thể thuộc về một kích cỡ
    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    // Quan hệ: Một biến thể thuộc về một loại đế
    public function crust()
    {
        return $this->belongsTo(Crust::class);
    }

    // Quan hệ: Một biến thể có nhiều item trong giỏ hàng
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Quan hệ: Một biến thể có nhiều item trong đơn hàng
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function comboItems()
    {
        return $this->hasMany(ComboItem::class, 'product_variant_id');
    }
}