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
        Schema::create('tbl_citymuni', function (Blueprint $table) {
            $table->string('psgc',10)->primary(); // Primary key, varchar(20)
            $table->string('col_citymuni', 45); // Name of the city/municipality, varchar(45)
            $table->integer('district');
            $table->string('province_psgc', 10); // Foreign key to tbl_province, varchar(20)
            
            // Foreign key constraint
            $table->foreign('province_psgc')->references('psgc')->on('tbl_province')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_citymuni');
    }
};
