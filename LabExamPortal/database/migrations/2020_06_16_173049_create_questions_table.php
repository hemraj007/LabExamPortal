<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');//primary key
            $table->string('title');
            $table->text('description');
            $table->integer('marks');
            $table->integer('admin_id')->unsigned();
            $table->integer('exam_id')->unsigned();
            $table->timestamps();
            $table->foreign('admin_id')->references('id')->on('users');
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
        Schema::dropIfExists('questions');
    }
}
