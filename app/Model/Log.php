<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = "logs";
    protected $fillable = ['user_id', 'type', 'description', 'status'];
}
