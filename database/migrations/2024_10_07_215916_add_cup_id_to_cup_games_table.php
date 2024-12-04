<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cup_games', function (Blueprint $table) {
            $table->foreignId('cup_id')->constrained('cups')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::table('cup_games', function (Blueprint $table) {
            $table->dropForeign(['cup_id']);
            $table->dropColumn('cup_id');
        });
    }
    
};
