<?php

// app/Models/Payment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public $fillable = ['order_id', 'amount', 'method', 'status', 'transaction_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}