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
    Schema::table('clubs', function (Blueprint $table) {
        $table->string('contact_person')->nullable();
        $table->string('phone_number')->nullable();
    });
}

public function down()
{
    Schema::table('clubs', function (Blueprint $table) {
        $table->dropColumn('contact_person');
        $table->dropColumn('phone_number');
    });
}
};
