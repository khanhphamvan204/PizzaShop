<?php

// app/Models/Combo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'start_date',
        'end_date',
        'is_active'
    ];

    public function items()
    {
        return $this->hasMany(ComboItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}