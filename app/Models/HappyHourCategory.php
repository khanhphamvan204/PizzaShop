<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyHourCategory extends Model
{
    protected $table = 'happy_hour_categories';
    protected $primaryKey = ['promotion_id', 'category_id'];
    public $incrementing = false;

    protected $fillable = [
        'promotion_id',
        'category_id',
    ];

    public function promotion()
    {
        return $this->belongsTo(HappyHourPromotion::class, 'promotion_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}