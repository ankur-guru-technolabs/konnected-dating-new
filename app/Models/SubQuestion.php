<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option',
    ];
}
