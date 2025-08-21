<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'total_amount', 'status', 'shipping_address', 'coupon_id'];

    protected $casts = [
        'status' => 'string', // ENUM được cast thành string
    ];

    // Quan hệ: Một đơn hàng thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ: Một đơn hàng thuộc về một coupon (có thể NULL)
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    // Quan hệ: Một đơn hàng có nhiều item
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Quan hệ: Một đơn hàng có một thanh toán
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function happyHourPromotion()
    {
        return $this->belongsTo(HappyHourPromotion::class, 'happy_hour_promotion_id');
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }
}