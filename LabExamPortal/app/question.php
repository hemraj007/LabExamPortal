<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class question extends Model
{
    //
    protected $table = 'questions';

    protected $fillable = [
        'title','description', 'marks','admin_id','exam_id',
    ];
}
