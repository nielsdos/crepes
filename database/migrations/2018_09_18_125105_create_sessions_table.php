<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('session_group_id')->index();
            $table->foreign('session_group_id')->references('id')->on('session_groups')->onDelete('cascade');
            $table->unsignedInteger('session_description_id');
            $table->foreign('session_description_id')->references('id')->on('session_descriptions')->onDelete('cascade');
            $table->string('location', 150);
            $table->dateTime('start')->index();
            $table->time('end');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
