<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $table = "interests";
    protected $fillable = ['title'];
}
