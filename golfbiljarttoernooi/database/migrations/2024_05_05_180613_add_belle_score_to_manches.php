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
            $table->integer('belle_score')->nullable(); // Assuming belle scores are integers and may not always be present
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
