<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\UserIceBreaker;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_no',
        'location',
        'latitude',
        'longitude',
        'live_latitude',
        'live_longitude',
        'job',
        'bio',
        'company',
        'gender',
        'age',
        'height',
        'education',
        'industry',
        'salary',
        'body_type',
        'children',
        'lastseen',
        'user_type',
        'faith',
        'ethnticity',
        'hobbies',
        'undo_remaining_count',
        'last_undo_date',
        'status',
        'email_verified',
        'phone_verified',
        'otp_verified',
        'is_notification_mute',
        'fcm_token',
        'device_token',
        'google_id',
        'facebook_id',
        'apple_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'age' => 'int',
        "gender"=>'int',
        "height"=>'int',
        "education"=>'int',
        "industry"=>'int',
        "salary"=>'int',
        "body_type"=>'int',
        "children"=>'int', 
        "faith"=>'int',
        'latitude'=>'float',
        'longitude'=>'float',
        'live_latitude'=>'float',
        'live_longitude'=>'float',
        'status'=> 'int',
        'email_verified' => 'int',
        'phone_verified'=> 'int',
        'otp_verified'=> 'int', 
        'is_notification_mute'=> 'int', 
    ];
    
    // FULL NAME

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    // RELATIONSHIPS

    public function iceBreakers()
    {
        return $this->hasMany(UserIceBreaker::class);
    }

    public function photos()
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function userQuestions()
    {
        return $this->hasMany(UserQuestion::class);
    }
}
