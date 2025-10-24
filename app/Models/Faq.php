<?php

// app/Models/Faq.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faq';

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['name', 'email', 'question', 'answer', 'status'];

    // Định nghĩa giá trị mặc định cho ENUM status
    protected $attributes = [
        'status' => 'pending',
    ];
}
?>