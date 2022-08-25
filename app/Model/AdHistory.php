<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AdHistory extends Model
{
    protected $table = "ad_history";
    protected $fillable = [
        'ad_id',
        'user_id',
        'last_seen',
        'seen_count'
    ];

    public function ad() {
        return $this->belongsTo('App\Model\Advertisement', 'ad_id', 'id');
    }
}
