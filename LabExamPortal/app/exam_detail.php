<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class exam_detail extends Model
{
    //
    protected $table = 'exam_details';

    protected $fillable = [
        'exam_id','exam_name','exam_hours','exam_for','exam_code','exam_date','exam_time',
    ];
}
