<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ReportReason extends Model
{
    protected $table = "report_reasons";
    protected $fillable = ['reason_text_en','reason_text_de','reason_text_tr'];
}
