<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = "user_address";

    protected $fillable = ['user_id', 'latitude', 'longitude', 'city', 'country'];

}
