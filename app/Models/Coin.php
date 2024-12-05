<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    use HasFactory;

    protected $fillable = [
        'coins',
        'price',
        'google_plan_id',
        'apple_plan_id',
    ];

    protected $casts = [
        'coins' => 'float',
        "price"=>'float',
    ];
}
