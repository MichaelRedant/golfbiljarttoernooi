<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('games', function (Blueprint $table) {
        $table->boolean('home_team_approved')->default(false);
        $table->boolean('away_team_approved')->default(false);
    });
}

public function down()
{
    Schema::table('games', function (Blueprint $table) {
        $table->dropColumn('home_team_approved');
        $table->dropColumn('away_team_approved');
    });
}
};
