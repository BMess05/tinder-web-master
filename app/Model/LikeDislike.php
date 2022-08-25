<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LikeDislike extends Model
{
    protected $table = "like_dislike";
    protected $fillable = ['sender_id', 'receiver_id', 'like_dislike', 'is_super'];

    public function receiver() {
        return $this->belongsTo('App\Model\User', 'receiver_id', 'id');
    }

    public function sender() {
        return $this->belongsTo('App\Model\User', 'sender_id', 'id');
    }
    
}
