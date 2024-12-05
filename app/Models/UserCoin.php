<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoin extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'coin_id',
        'price',
        'coins_number',
        'message',
        'type',
        'action',
    ];

    protected $casts = [
        'coins_number' => 'float',
        "price"=>'float',
    ];
}
