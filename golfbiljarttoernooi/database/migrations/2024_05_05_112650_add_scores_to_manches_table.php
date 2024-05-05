<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('manches', function (Blueprint $table) {
        $table->integer('score1')->nullable();
        $table->integer('score2')->nullable();
    });
}

public function down()
{
    Schema::table('manches', function (Blueprint $table) {
        $table->dropColumn(['score1', 'score2']);
    });
}

};
