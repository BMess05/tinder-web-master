<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdPreference extends Model
{
    protected $table = "ad_preferences";
    protected $fillable = [
        'ad_id',
        'latitude',
        'longitude',
        'age_from',
        'age_to',
        'gender_group',
        'address',
        'diameter'
    ];

    public function advertisement() {
        return $this->belongsTo('App\Model\Advertisement', 'ad_id', 'id');
    }


}
