<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = "reports";
    protected $fillable = ['reported_by', 'reported_id', 'is_reported'];

    public function reported_by() {
        return $this->belongsTo('App/Model/User', 'reported_by', 'id');
    }

    public function reported_ids() {
        return $this->belongsTo('App/Model/User', 'reported_id', 'id');
    }
}
