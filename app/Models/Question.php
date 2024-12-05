<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubQuestion;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question'
    ];

    public function SubQuestions() 
    {
        return $this->hasMany(SubQuestion::class);
    }
}
