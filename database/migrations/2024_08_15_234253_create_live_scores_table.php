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
    Schema::create('live_scores', function (Blueprint $table) {
        $table->id();
        $table->foreignId('game_id')->constrained()->onDelete('cascade');
        $table->json('data'); // Hier slaan we alle wedstrijdgegevens op
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('live_scores');
}

};
