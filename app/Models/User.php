<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = ['username', 'password', 'email', 'full_name', 'address', 'phone', 'role'];
    
    protected $casts = [
        'role' => 'string', // ENUM được cast thành string
    ];

    // Quan hệ: Một user có nhiều đơn hàng
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Quan hệ: Một user có nhiều giỏ hàng
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Quan hệ: Một user có nhiều đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Quan hệ: Một user có nhiều liên hệ
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
}