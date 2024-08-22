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
        Schema::table('player_season_stats', function (Blueprint $table) {
            // Voeg de kolommen toe als ze niet bestaan
            if (!Schema::hasColumn('player_season_stats', 'manches_won')) {
                $table->integer('manches_won')->default(0);
            }
            if (!Schema::hasColumn('player_season_stats', 'manches_lost')) {
                $table->integer('manches_lost')->default(0);
            }
            if (!Schema::hasColumn('player_season_stats', 'matches_won')) {
                $table->integer('matches_won')->default(0);
            }
            if (!Schema::hasColumn('player_season_stats', 'matches_lost')) {
                $table->integer('matches_lost')->default(0);
            }
            if (!Schema::hasColumn('player_season_stats', 'points')) {
                $table->integer('points')->default(0);
            }
            if (!Schema::hasColumn('player_season_stats', 'season_id')) {
                $table->unsignedBigInteger('season_id');
                $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
            }
            if (!Schema::hasColumn('player_season_stats', 'player_id')) {
                $table->unsignedBigInteger('player_id');
                $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_season_stats', function (Blueprint $table) {
            // Optioneel: Kolommen verwijderen bij rollback
            $table->dropColumn(['manches_won', 'manches_lost', 'matches_won', 'matches_lost', 'points']);
        });
    }
};
