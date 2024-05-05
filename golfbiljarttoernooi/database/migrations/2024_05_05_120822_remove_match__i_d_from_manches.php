<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMatchIdFromManches extends Migration
{
    public function up()
    {
        Schema::table('manches', function (Blueprint $table) {
            // Verwijder de foreign key constraint
            $table->dropForeign(['match_id']);
            
            // Nu kun je veilig de kolom verwijderen
            $table->dropColumn('match_id');
        });
    }

    public function down()
    {
        Schema::table('manches', function (Blueprint $table) {
            // Voeg de kolom opnieuw toe
            $table->unsignedBigInteger('match_id')->nullable();
            
            // Voeg de foreign key constraint opnieuw toe
            $table->foreign('match_id')->references('id')->on('matches')->onDelete('set null');
        });
    }
}
