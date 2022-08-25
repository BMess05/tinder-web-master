<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = "chat_messages";
    protected $fillable = ['user_id', 'thread_id', 'message', 'image', 'is_read'];
    
    public function match() {
        return $this->hasMany('App\Model\Match', 'thread_id', 'thread_id');
    }

    public function sender() {
        return $this->belongsTo('App\Model\User', 'user_id', 'id');
    }
}
