<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionReceipts extends Model
{
    // use SoftDeletes;

    protected $dates = ['deleted_at','created_at','updated_at'];
    protected $table = 'subscription_receipts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                            'id',                            
                            'subscription_id',
                            'user_id',
                            'receipt_data',
                            'created_at','updated_at',
                            'is_active'
                        ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [ 'created_at','updated_at','deleted_at' ,'laravel_through_key'];

    protected $appends = [];
    protected $with = [];
}
