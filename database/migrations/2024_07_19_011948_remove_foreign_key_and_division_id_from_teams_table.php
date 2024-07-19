<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveForeignKeyAndDivisionIdFromTeamsTable extends Migration
{
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['division_id']); // verwijder de foreign key constraint
            $table->dropColumn('division_id');    // verwijder de kolom
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable();
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }
}
