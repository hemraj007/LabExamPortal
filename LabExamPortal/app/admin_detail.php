<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class admin_detail extends Model
{
    //
    protected $table = 'admin_details';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_id','course_code', 'course_name','instructor_name',
    ];
}
