<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['order_id', 'amount', 'method', 'status', 'transaction_id'];

    protected $casts = [
        'method' => 'string',
        'status' => 'string',
    ];

    // Quan hệ: Một thanh toán thuộc về một đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}