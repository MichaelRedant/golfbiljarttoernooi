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
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']); // Verwijder de unieke beperking van het email-veld
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email'); // Voeg de unieke beperking weer toe bij rollback
        });
    }
};
