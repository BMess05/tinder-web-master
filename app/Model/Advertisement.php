<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $table = "advertisements";
    protected $fillable = [
        'title',
        'description',
        'company_id',
        'image',
        'url',
        'is_active'
    ];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute() {
        if($this->attributes['image'] == "") {
            return null;
        }
        return url('/uploads/adds/'.$this->attributes['image']);
    }

    public function ad_preference() {
        return $this->hasOne('App\Model\AdPreference', 'ad_id', 'id');
    }

    public function ad_history() {
        return $this->hasMany('App\Model\AdHistory', 'ad_id', 'id');
    }

    public function company() {
        return $this->belongsTo('App\Model\Company', 'company_id', 'id');
    }

}
