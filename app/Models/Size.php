<?php

// app/Models/Size.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    protected $fillable = ['name', 'diameter'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}