<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_province', function (Blueprint $table) {
            $table->string('psgc', 10)->primary(); // Primary key, varchar(20)
            $table->string('col_province', 20)->nullable(); // col_province, varchar(45), nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_province');
    }
};

