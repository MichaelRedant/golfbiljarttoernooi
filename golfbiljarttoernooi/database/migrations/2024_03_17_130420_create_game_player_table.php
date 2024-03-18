<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamePlayerTable extends Migration
{
    public function up()
    {
        Schema::create('game_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->integer('manche_1_score')->default(0)->comment('Score voor de eerste manche');
            $table->integer('manche_2_score')->default(0)->comment('Score voor de tweede manche');
            $table->integer('belle_score')->default(0)->comment('Score voor de belle, indien van toepassing');
            $table->boolean('is_belle_winner')->default(false)->comment('Of de speler de belle heeft gewonnen');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('game_player');
    }
}
