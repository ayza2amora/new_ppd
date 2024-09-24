<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUtilizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('utilizations', function (Blueprint $table) {
            $table->id();
            
            // Foreign key for province (from tbl_province)
            $table->string('province', 10); // Adjust the length to match psgc in tbl_province
            $table->foreign('province')->references('psgc')->on('tbl_province')->onDelete('cascade');
            
            // Foreign key for city_municipality (from tbl_citymuni)
            $table->string('city_municipality', 10); // Adjust the length to match psgc in tbl_citymuni
            $table->foreign('city_municipality')->references('psgc')->on('tbl_citymuni')->onDelete('cascade');
            
             // Foreign key for program (from programs table)
             $table->unsignedBigInteger('program')->nullable(); // Allow null since it's using onDelete('set null')
             $table->foreign('program')->references('id')->on('programs')->onDelete('set null');
            
            $table->integer('physical');
            $table->decimal('fund_utilized', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utilizations', function (Blueprint $table) {
            // Drop the foreign key constraints
            $table->dropForeign(['province']);
            $table->dropForeign(['city_municipality']);
            $table->dropForeign(['program']);
        });
        
        Schema::dropIfExists('utilizations');
    }
}
