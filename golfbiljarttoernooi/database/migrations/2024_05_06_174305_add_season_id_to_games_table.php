<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSeasonIdToGamesTable extends Migration
{
    public function up()
    {
        // Voeg de season_id kolom toe
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('season_id')->after('id'); // Voeg de kolom toe na de 'id' kolom
        });

        // Update bestaande records om een standaard 'season_id' te hebben
        DB::table('games')->update(['season_id' => 1]); // Zorg ervoor dat 1 de ID is van je standaard seizoen

        // Voeg de foreign key constraint toe
        Schema::table('games', function (Blueprint $table) {
            $table->foreign('season_id')->references('id')->on('seasons')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Verwijder de foreign key en de kolom bij het terugdraaien van de migratie
        Schema::table('games', function (Blueprint $table) {
            $table->dropForeign(['season_id']);
            $table->dropColumn('season_id');
        });
    }
}
