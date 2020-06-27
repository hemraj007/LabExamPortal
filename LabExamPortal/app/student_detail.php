<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class student_detail extends Model
{
    //
    protected $table = 'student_details';

    protected $fillable = [ 
        'student_id','name', 'course',
    ];
}
