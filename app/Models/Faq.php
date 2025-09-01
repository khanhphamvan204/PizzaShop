<?php

// app/Models/Faq.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $table = 'faq';
    protected $hidden = ['created_at', 'updated_at'];
    protected $fillable = ['question', 'answer'];
}