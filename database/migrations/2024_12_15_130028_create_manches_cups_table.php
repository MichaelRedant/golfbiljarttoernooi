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
    Schema::create('manches_cups', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('cup_game_id'); // Koppeling aan cup_games
        $table->unsignedBigInteger('player1_id')->nullable(); // Eerste speler
        $table->unsignedBigInteger('player2_id')->nullable(); // Tweede speler
        $table->unsignedTinyInteger('score1')->default(0); // Score van speler 1
        $table->unsignedTinyInteger('score2')->default(0); // Score van speler 2
        $table->unsignedTinyInteger('belle_score')->nullable(); // Optionele Belle-score
        $table->unsignedBigInteger('winner_id')->nullable(); // Winnaar
        $table->unsignedBigInteger('forfeit_by_player')->nullable(); // Forfait door speler
        $table->unsignedInteger('number'); // Manche nummer
        $table->timestamps();

        // Foreign keys
        $table->foreign('cup_game_id')->references('id')->on('cup_games')->onDelete('cascade');
        $table->foreign('player1_id')->references('id')->on('players')->onDelete('set null');
        $table->foreign('player2_id')->references('id')->on('players')->onDelete('set null');
        $table->foreign('winner_id')->references('id')->on('players')->onDelete('set null');
        $table->foreign('forfeit_by_player')->references('id')->on('players')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manches_cups');
    }
};
