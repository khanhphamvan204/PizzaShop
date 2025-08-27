<?php

// app/Models/Coupon.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'discount_percentage',
        'discount_amount',
        'expiry_date',
        'min_order_amount',
        'max_discount_amount',
        'is_active'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}