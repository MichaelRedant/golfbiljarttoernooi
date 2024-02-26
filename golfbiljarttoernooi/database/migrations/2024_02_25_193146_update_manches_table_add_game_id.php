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
        Schema::table('manches', function (Blueprint $table) {
            // Voeg de game_id kolom toe en maak een foreign key constraint
            $table->unsignedBigInteger('game_id')->after('id')->nullable();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manches', function (Blueprint $table) {
            //
        });
    }
};
