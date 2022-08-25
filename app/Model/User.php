<?php

namespace App\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    // use SoftDeletes;
    use Notifiable;

    protected $table = "users";

    protected $fillable = ['name', 'email', 'password', 'type', 'dob', 'gender', 'university', 'business', 'interested_in', 'image', 'is_verified', 'latitude', 'longitude', 'about_me', 'city', 'company', 'interests', 'app_notification', 'email_verification_token','otp_expiration_time','virtual_longitude','virtual_latitude','virtual_city'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','created_at','updated_at','deleted_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function user_type() {
        return $this->hasMany('App\Model\UserImage', 'user_id', 'id');
    }

    public function user_images() {
        return $this->hasMany('App\Model\UserImage', 'user_id', 'id');
    }

    public function reported_by() {
        return $this->hasMany('App\Model\Report', 'reported_by', 'id');
    }

    public function reported_ids() {
        return $this->hasMany('App\Model\Report', 'reported_id', 'id');
    }

    public function match_person_1() {
        return $this->hasMany('App\Model\UserImage', 'person_1', 'id');
    }

    public function match_person_2() {
        return $this->hasMany('App\Model\UserImage', 'person_2', 'id');
    }

    public function chat_messages() {
        return $this->hasMany('App\Model\ChatMessage', 'user_id', 'id');
    }

    public function subscriptions() {
        return $this->hasOne('App\Model\Subscriptions', 'user_id', 'id') 
                    ->where('subscriptions.is_active',1);
    }

    /**
     * Get available subscription feature details
     */
    public function user_subscription_features()
    {
        return $this->hasOneThrough(
            'App\Model\SubscriptionFeaturesStatus',
            'App\Model\Subscriptions',
            'user_id', // Foreign key on the subscriptions table...
            'subscription_id', // Foreign key on the subscription_features_status table...
            'id', // Local key on the users table...
            'id' // Local key on the subscriptions table...
        )->where('subscriptions.is_active',1)
         ->where('subscription_features_status.is_active',1);
    }

    /**
     * Get available subscription feature details
     */
    public function subscription_plan()
    {
        return $this->hasOneThrough(
            'App\Model\SubscriptionPlans',
            'App\Model\Subscriptions',
            'user_id', // Foreign key on the subscriptions table...
            'id', // Foreign key on the subscription_plans table...
            'id', // Local key on the users table...
            'plan_id' // Local key on the subscriptions table...
        );
    }

    /**
     * Get available subscription receipt
     */
    public function subscription_receipt()
    {
        return $this->hasOneThrough(
            'App\Model\SubscriptionReceipts',
            'App\Model\Subscriptions',
            'user_id', // Foreign key on the subscriptions table...
            'user_id', // Foreign key on the subscription_receipts table...
            'id', // Local key on the users table...
            'user_id' // Local key on the subscriptions table...
        )
        ;
    }

    /**
     * Get consumables
     */
    public function consumables()
    {
        return $this->hasMany('App\Model\ConsumableFeatures', 'user_id', 'id');
    }

    /**
     * Get user device tokens
     */
    public function user_device_tokens()
    {
        return $this->hasMany('App\Model\DeviceToken', 'user_id', 'id');
    }
}
