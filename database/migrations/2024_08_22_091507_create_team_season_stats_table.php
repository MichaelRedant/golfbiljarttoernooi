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
        Schema::create('team_season_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');  // Verwijst naar het team
            $table->foreignId('season_id')->constrained()->onDelete('cascade');  // Verwijst naar het seizoen
            $table->integer('games_won')->default(0);  // Aantal gewonnen wedstrijden in dit seizoen
            $table->integer('games_lost')->default(0);  // Aantal verloren wedstrijden in dit seizoen
            $table->integer('games_draw')->default(0);  // Aantal gelijke spelen in dit seizoen
            $table->integer('points')->default(0);  // Punten behaald in dit seizoen
            $table->timestamps();  // Tijdstempels voor aanmaak en bijwerken
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_season_stats');
    }
};
