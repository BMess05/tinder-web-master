<?php

namespace App\Model;
use Auth;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $table = "matches";
    protected $fillable = ['thread_id', 'person_1', 'person_2'];

    public function user_person1() {
        return $this->belongsTo('App\Model\User', 'person_1', 'id');
    }

    public function user_person2() {
        return $this->belongsTo('App\Model\User', 'person_2', 'id');
    }

    public function thread_messages() {
        return $this->hasMany('App\Model\ChatMessage', 'thread_id', 'thread_id');
    }

    public function unread_thread_messages() {

        $user = \Auth::user();
        return $this->hasMany('App\Model\ChatMessage', 'thread_id', 'thread_id')->where('chat_messages.is_read','=',0)->where('chat_messages.user_id','!=',$user->id);
        
    }
}
