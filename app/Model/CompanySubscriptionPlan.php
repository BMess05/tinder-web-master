<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompanySubscriptionPlan extends Model
{
    protected $table = 'company_subscription_plans';
    protected $fillable = [
        'name',
        'slug',
        'interval',
        'stripe_plan',
        'stripe_product',
        'cost',
        'description',
        'active'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
