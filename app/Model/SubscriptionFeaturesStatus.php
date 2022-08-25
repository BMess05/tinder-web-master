<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionFeaturesStatus extends Model
{
    // use SoftDeletes;

    protected $dates = ['deleted_at','created_at','updated_at',
                        'last_boosted_on',
                        'last_liked_on',
                        'last_super_liked_on',
                        'last_last_liked_on',
                        'last_top_picked_on',
                    ];
    protected $table = 'subscription_features_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 
                            'id',
                            'subscription_id',
                            'available_boost',
                            'last_boosted_on',
                            'boost_reset_on',
                            'available_likes',
                            'last_liked_on',
                            'likes_reset_on',
                            'available_super_likes',
                            'last_super_liked_on',
                            'super_likes_reset_on',
                            'available_last_likes',
                            'last_last_liked_on',
                            'last_likes_reset_on',
                            'available_top_picked',
                            'visible_top_picks',
                            'last_top_picked_on',
                            'top_picked_reset_on',
                            'rewind',
                            'created_at','updated_at',
                            'is_active'
                        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ 'created_at','updated_at', 'deleted_at' ,'laravel_through_key'];

    protected $appends = [

        'last_boosted_on_ms',
        // 'boost_reset_on_ms',

        // 'last_liked_on_ms',
        // 'likes_reset_on_ms',

        // 'last_super_liked_on_ms',
        // 'super_likes_reset_on_ms',

        // 'last_top_picked_on_ms',
        // 'top_picked_reset_on_ms'
    ];
    protected $with = [];



    /**
     * Get last boosted time time in ms
     *
     */
    public function getLastBoostedOnMsAttribute()
    {
        if( $this->last_boosted_on != null ){
            return $this->last_boosted_on->timestamp;
        }else{
            return null;
        }         
    }


    /**
     * Get boosted reset time in ms
     *
     */
    // public function getBoostResetOnMsAttribute()
    // {
    //     if( $this->boost_reset_on != null ){
    //         return $this->boost_reset_on->timestamp;
    //     }else{
    //         return null;
    //     }
    // }

}
