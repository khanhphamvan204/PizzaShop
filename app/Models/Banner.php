<?php

// app/Models/Banner.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image_url', 'link', 'position', 'active'];
    protected $hidden = ['created_at', 'updated_at'];
}