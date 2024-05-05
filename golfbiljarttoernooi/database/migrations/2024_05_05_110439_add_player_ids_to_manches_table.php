<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('manches', function (Blueprint $table) {
        $table->unsignedBigInteger('player1_id')->after('game_id')->nullable();
        $table->unsignedBigInteger('player2_id')->after('player1_id')->nullable();
        $table->foreign('player1_id')->references('id')->on('players');
        $table->foreign('player2_id')->references('id')->on('players');
    });
}

public function down()
{
    Schema::table('manches', function (Blueprint $table) {
        $table->dropForeign(['player1_id']);
        $table->dropForeign(['player2_id']);
        $table->dropColumn('player1_id');
        $table->dropColumn('player2_id');
    });
}

};
