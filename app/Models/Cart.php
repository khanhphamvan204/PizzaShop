<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    // Quan hệ: Một giỏ hàng thuộc về một user (có thể NULL)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ: Một giỏ hàng có nhiều item
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}