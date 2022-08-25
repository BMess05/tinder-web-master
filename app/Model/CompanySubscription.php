<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompanySubscription extends Model
{
    protected $table = 'company_subscriptions';
    protected $fillable = [
        'user_id', // Company Id
        'name',
        'stripe_id',
        'stripe_status',
        'stripe_plan',
        'quantity',
        'trial_ends_at',
        'starts_at',
        'ends_at'
    ];

    public function user() {
        return $this->belongsTo('App\Model\Company', 'user_id', 'id');
    }
}
