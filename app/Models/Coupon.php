<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'discount_percentage', 'discount_amount', 'expiry_date', 'min_order_amount'];

    // Quan hệ: Một coupon áp dụng cho nhiều đơn hàng
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}