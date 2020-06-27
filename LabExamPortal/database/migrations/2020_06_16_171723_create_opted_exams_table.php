<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptedExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opted_exams', function (Blueprint $table) {
            $table->integer('student_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->bigInteger('duration_left');//remaining time in ms
            $table->timestamps();
            $table->primary(['student_id','exam_id']);
            $table->foreign('student_id')->references('id')->on('users');
            $table->foreign('exam_id')->references('exam_id')->on('exam_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opted_exams');
    }
}
