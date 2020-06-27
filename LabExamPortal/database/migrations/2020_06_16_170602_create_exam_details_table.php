<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_details', function (Blueprint $table) {
            $table->increments('exam_id');
            $table->string('exam_name');
            $table->integer('exam_hours');
            $table->integer('exam_for')->unsigned();
            $table->integer('exam_code');
            $table->timestamps();
            $table->foreign('exam_for')->references('admin_id')->on('admin_details');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_details');
    }
}
