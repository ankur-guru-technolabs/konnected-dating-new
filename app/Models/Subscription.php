<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'search_filters',
        'like_per_day',
        'video_call',
        'who_like_me',
        'who_view_me',
        'undo_profile',
        'read_receipt',
        'travel_mode',
        'profile_badge',
        'price',
        'month',
        'plan_duration',
        'plan_type',
        'google_plan_id',
        'apple_plan_id'
    ];


    protected $casts = [
        'price'=>'float', 
    ];
}
