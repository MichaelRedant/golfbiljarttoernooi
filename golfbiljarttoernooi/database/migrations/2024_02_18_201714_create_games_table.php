<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
    $table->date('date');
    $table->string('home_team');
    $table->string('away_team');
    $table->integer('home_score')->nullable();
    $table->integer('away_score')->nullable();
    $table->time('start_time');
    $table->boolean('home_forfeit')->default(false);
    $table->boolean('away_forfeit')->default(false);
    $table->date('other_date')->nullable(); // New column for other date
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
