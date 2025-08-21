<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'image_url', 'category_id'];

    // Quan hệ: Một sản phẩm thuộc về một danh mục
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Quan hệ: Một sản phẩm có nhiều biến thể
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Quan hệ: Một sản phẩm có nhiều đánh giá
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function happyHourPromotions()
    {
        return $this->hasMany(HappyHourProduct::class, 'product_id');
    }
}