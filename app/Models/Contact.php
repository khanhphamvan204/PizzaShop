<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'message'];

    // Quan hệ: Một liên hệ thuộc về một user (có thể NULL)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}