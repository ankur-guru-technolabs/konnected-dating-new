<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'question_id',
        'answer_id',
    ];

    protected $casts = [
        'user_id' => 'int',
        "question_id"=>'int',
        "answer_id"=>'int',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
