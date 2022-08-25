<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlans extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $table = 'subscription_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'id',
                            "plan_name",
                            'super_likes','super_likes_count','super_likes_duration',
                            'unlimited_likes','likes_count',
                            'boost_count','boost_duration',
                            'passport',
                            'unlimited_rewinds',
                            'ads',
                            'top_picks',
                            'top_picks_visible',
                            'top_picks_count',
                            'top_picks_duration',
                            'see_who_likes_me',
                            'last_likes',
                            'last_likes_duration',
                            'priority_likes',
                            'attach_message',
                            'product_id',
                            'apple_id',
                            'android_id',
                            'availability',
                            'reference_name',
                            'price',
                            'offer_price',
                            'grace_period',
                            'start_date',
                            'end_date',
                            'created_at','updated_at',
                            'is_active'
                        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ 'created_at','updated_at', 'deleted_at' ,'laravel_through_key'];

    protected $appends = [];
    protected $with = [];


    
}
