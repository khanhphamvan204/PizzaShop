<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyHourPromotion extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'start_time',
        'end_time',
        'days_of_week',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'days_of_week' => 'array', // Chuyển SET thành array trong Laravel
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(HappyHourCategory::class, 'promotion_id');
    }

    public function products()
    {
        return $this->hasMany(HappyHourProduct::class, 'promotion_id');
    }
}