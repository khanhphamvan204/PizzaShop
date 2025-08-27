<?php

// app/Models/ComboItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    protected $fillable = ['combo_id', 'product_variant_id', 'quantity'];

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}