<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReviewLater extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_review_from',
        'user_review_to',
    ];

    public function users()
    {
        return $this->hasMany(User::class,'id','user_review_to');
    }
}
