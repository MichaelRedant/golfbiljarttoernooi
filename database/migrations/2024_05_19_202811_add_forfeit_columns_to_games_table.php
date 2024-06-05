<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForfeitColumnsToGamesTable extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('forfeit_by')->nullable()->after('division_id');
            $table->boolean('forfeit_confirmed')->default(false)->after('forfeit_by');
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('forfeit_by');
            $table->dropColumn('forfeit_confirmed');
        });
    }
}

