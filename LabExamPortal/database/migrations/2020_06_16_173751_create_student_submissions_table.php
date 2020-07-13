<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_submissions', function (Blueprint $table) {
            $table->integer('student_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->integer('qid')->unsigned();
            $table->boolean('is_attempted')->default(0);
            $table->text('source_code')->nullable();
            $table->string('lang')->nullable();
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->integer('marks')->nullable();
            $table->timestamp('submission_time')->nullable();
            $table->integer('no_of_submissions');
            $table->timestamps();
            $table->primary(['student_id','exam_id','qid']);
            $table->foreign('student_id')->references('id')->on('users');
            $table->foreign('exam_id')->references('exam_id')->on('exam_details');
            $table->foreign('qid')->references('id')->on('questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_submissions');
    }
}
