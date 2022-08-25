<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriptions extends Model
{
    // use SoftDeletes;

    protected $dates = [
        'deleted_at', 'created_at', 'updated_at',
        'purchase_date', 'original_purchase_date', 'expires_date'
    ];
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'plan_id',
        'platform',
        'apple_id',
        'android_id',

        'user_id',

        'product_id',
        'quantity',
        'transaction_id',
        'original_transaction_id',
        'purchase_date',
        'original_purchase_date',
        'expires_date',
        'is_trial_period',
        'is_in_intro_offer_period',
        'web_order_line_item_id',
        'subscription_group_identifier',
        'is_active',
        'purchase_token',

        'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'laravel_through_key'];

    protected $appends = [];
    protected $with = [];


    public function user_features_status()
    {
        return $this->hasOne('App\Model\SubscriptionFeaturesStatus', 'subscription_id', 'id');
    }

    public function subscription_plan()
    {
        return $this->hasOne('App\Model\SubscriptionPlans', 'id', 'plan_id');
    }

    public function consumables()
    {
        return $this->hasMany('App\Model\ConsumableFeatures', 'user_id', 'user_id');
    }
}
