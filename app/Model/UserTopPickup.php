<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserTopPickup extends Model
{
    
    protected $table = "user_top_pickups";
    
    protected $fillable = ['id','user_id', 'pick_up_users', 'pickup_date'];
    
}
