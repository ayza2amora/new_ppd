<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropProgramColumnFromClientLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_logs', function (Blueprint $table) {
            // First, drop the foreign key constraint
            $table->dropForeign(['program']);

            // Now, drop the 'program' column
            $table->dropColumn('program');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_logs', function (Blueprint $table) {
            // Add the 'program' column back
            $table->unsignedBigInteger('program')->nullable();

            // Recreate the foreign key constraint
            $table->foreign('program')->references('id')->on('programs')->onDelete('cascade');
        });
    }
}