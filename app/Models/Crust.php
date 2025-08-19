<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crust extends Model
{
    protected $fillable = ['name', 'description'];

    // Quan hệ: Một loại đế áp dụng cho nhiều biến thể
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}