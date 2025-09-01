<?php

// app/Models/Crust.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crust extends Model
{
    protected $fillable = ['name', 'description'];
    protected $hidden = ['created_at', 'updated_at'];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}