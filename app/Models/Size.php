<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = ['name', 'diameter'];

    // Quan hệ: Một kích cỡ áp dụng cho nhiều biến thể
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}