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
    Schema::table('games', function (Blueprint $table) {
        $table->unsignedBigInteger('home_team_id')->nullable()->change();
        $table->unsignedBigInteger('away_team_id')->nullable()->change();
    });
}

public function down()
{
    Schema::table('games', function (Blueprint $table) {
        $table->unsignedBigInteger('home_team_id')->nullable(false)->change();
        $table->unsignedBigInteger('away_team_id')->nullable(false)->change();
    });
}
};
