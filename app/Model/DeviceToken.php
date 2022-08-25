<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = "device_token";
    protected $fillable = ['user_id', 'device_token', 'device_type'];
}
