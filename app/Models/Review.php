<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['product_id', 'user_id', 'rating', 'comment'];

    // Quan hệ: Một đánh giá thuộc về một sản phẩm
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Quan hệ: Một đánh giá thuộc về một user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}