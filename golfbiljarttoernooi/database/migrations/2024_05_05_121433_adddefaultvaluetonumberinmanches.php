<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('manches', function (Blueprint $table) {
            $table->integer('number')->default(0)->change();  // Stel een standaardwaarde in
        });
    }

    public function down()
    {
        Schema::table('manches', function (Blueprint $table) {
            $table->integer('number')->change();
        });
    }
};
