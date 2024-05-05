<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWinnerIdToGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('winner_id')->nullable()->after('away_score');

            // Zorg ervoor dat 'winner_id' een foreign key is die verwijst naar de 'id' in de 'teams' tabel
            $table->foreign('winner_id')->references('id')->on('teams')->onDelete('set null');
        });
    }

    /**
     * Rollback the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            // Verwijder de foreign key voordat je de kolom verwijdert
            $table->dropForeign(['winner_id']);
            $table->dropColumn('winner_id');
        });
    }
}
