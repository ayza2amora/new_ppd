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
        Schema::table('client_logs', function (Blueprint $table) {
            // Add the 'program' column which will store the program ID
            $table->unsignedBigInteger('program')->nullable()->after('user_id');
    
            // Add the foreign key constraint
            $table->foreign('program')->references('id')->on('programs')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::table('client_logs', function (Blueprint $table) {
            // Drop the foreign key constraint and the 'program' column
            $table->dropForeign(['program']);
            $table->dropColumn('program');
        });
    }
};
