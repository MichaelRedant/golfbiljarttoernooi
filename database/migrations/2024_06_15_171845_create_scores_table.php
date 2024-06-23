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
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->foreignId('home_player_id')->nullable()->constrained('players');
            $table->foreignId('away_player_id')->nullable()->constrained('players');
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->integer('belle_score')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('players');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scores');
    }
};
