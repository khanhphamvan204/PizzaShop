<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image_url', 'link', 'position', 'active'];

    protected $casts = [
        'position' => 'string',
        'active' => 'boolean',
    ];
}