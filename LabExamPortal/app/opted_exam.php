<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class opted_exam extends Model
{
    //
    protected $table = 'opted_exams';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id','exam_id', 'duration_left',
    ];
}
