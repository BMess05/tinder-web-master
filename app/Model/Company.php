<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;

class Company extends Authenticatable
{
    use Billable;
    protected $table = 'companies';
    protected $fillable = [
        'company_name',
        'contact_name',
        'password',
        'address',
        'profile_picture',
        'active',
        'is_verified',
        'email',
        'password_reset_token',
        'stripe_id',
        'card_brand',
        'card_last_four',
        'trial_ends_at',
        'subscription'
    ];

    public function stripe_subscription() {
        return $this->hasOne('App\Model\CompanySubscription', 'user_id', 'id')->latest();
    }

    public function advertisements() {
        return $this->hasMany('App\Model\Advertisement', 'company_id', 'id');
    }
}
