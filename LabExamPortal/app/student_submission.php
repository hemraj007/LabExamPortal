<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class student_submission extends Model
{
    //
    protected $table = 'student_submissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id','exam_id', 'qid','no_of_submissions',
    ];
}
