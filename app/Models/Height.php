<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Height extends Model
{
    use HasFactory;

    protected $fillable = [
        'height',
    ];

    protected $appends = ['formatted_height'];

    // ACCESSOR

    public function getFormattedHeightAttribute()
    {
        $height = explode('.',$this->height);
        if(isset($height[1])){
            return $height[0]."'".$height[1];
        }
        return $height[0]."'0";
    }
}
