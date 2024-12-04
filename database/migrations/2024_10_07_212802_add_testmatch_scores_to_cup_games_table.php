<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('cup_games', function (Blueprint $table) {
        $table->integer('testmatch_score_home')->nullable();
        $table->integer('testmatch_score_away')->nullable();
    });
}

};
