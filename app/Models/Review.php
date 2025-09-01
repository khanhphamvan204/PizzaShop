<?php

// app/Models/Review.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['product_id', 'combo_id', 'user_id', 'rating', 'comment'];
    protected $hidden = ['created_at', 'updated_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}