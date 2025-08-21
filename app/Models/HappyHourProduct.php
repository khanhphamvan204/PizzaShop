<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyHourProduct extends Model
{
    protected $table = 'happy_hour_products';
    protected $primaryKey = ['promotion_id', 'product_id'];
    public $incrementing = false;

    protected $fillable = [
        'promotion_id',
        'product_id',
    ];

    public function promotion()
    {
        return $this->belongsTo(HappyHourPromotion::class, 'promotion_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}