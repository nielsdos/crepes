<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->fullText('lastname');
            $table->fullText('firstname');
            $table->fullText('function');
            $table->fullText('member_nr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropFullText('lastname');
            $table->dropFullText('firstname');
            $table->dropFullText('function');
            $table->dropFullText('member_nr');
        });
    }
};
