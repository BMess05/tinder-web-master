<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    // use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'feedbacks';

    protected $fillable = [ 'id', 'user_id', 'title', 'description', 'deleted_at', 'created_at', 'updated_at' ];


    public function feedbackUser() {
        return $this->hasOne('App\Model\User', 'id', 'user_id');
    }

}
