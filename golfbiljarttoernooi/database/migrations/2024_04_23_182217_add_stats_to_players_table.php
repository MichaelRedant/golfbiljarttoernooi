<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatsToPlayersTable extends Migration
{
    public function up()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->integer('matches_won')->default(0);
            $table->integer('matches_lost')->default(0);
            $table->integer('manches_won')->default(0);
            $table->integer('manches_lost')->default(0);
        });
    }

    public function down()
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('matches_won');
            $table->dropColumn('matches_lost');
            $table->dropColumn('manches_won');
            $table->dropColumn('manches_lost');
        });
    }
}

