<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserIceBreaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ice_breaker_id',
        'answer',
    ];

    protected $casts = [
        'user_id' => 'int',
        "ice_breaker_id"=>'int',
    ];
}
