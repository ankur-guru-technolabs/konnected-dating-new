<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLikes extends Model
{
    use HasFactory;

    protected $fillable = [
        'like_from',
        'like_to',
        'match_id',
        'match_status',
        'status',
        'can_chat',
        'matched_at',
    ];

    public function users()
    {
        return $this->hasMany(User::class,'id','like_from');
    }
   
    public function usersLikesTo()
    {
        return $this->hasMany(User::class,'id','like_to');
    }
}
