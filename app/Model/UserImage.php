<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserImage extends Model
{
    protected $table = "user_images";
    protected $fillable = ['user_id', 'image_name', 'is_main'];
    public function user_images() {
        return $this->belongsTo('App/Model/User', 'user_id', 'id');
    }
}
