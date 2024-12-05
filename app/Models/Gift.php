<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'coin',
    ];

    protected $casts = [
        "coin"=>'float',
    ];

    protected $appends = ['gift_image'];

    // ACCESSOR

    public function getGiftImageAttribute()
    {
        return asset('/gift/' . $this->image);
    }
}
