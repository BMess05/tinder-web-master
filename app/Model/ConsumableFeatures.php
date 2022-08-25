<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class ConsumableFeatures extends Model
{
    // use SoftDeletes;

    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $table = 'consumable_features';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'id',
                            'platform',
                            'apple_id',
                            'android_id',
                            'user_id',                       
                            'consumable_quantity',
                            'consumable_type',                          
                            'is_active',                            
                            'created_at','updated_at'
                        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ 'created_at','updated_at','deleted_at','laravel_through_key' ];

    protected $appends = [];
    protected $with = [];

}
