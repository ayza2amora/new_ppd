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
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // The user on whom the action was performed
            $table->string('action',50); // The action (e.g., 'approved', 'role_change', 'activated', 'deactivated')
            $table->string('previous_value',100)->nullable(); // The previous role/program value
            $table->string('new_value',100)->nullable();      // The new role/program value
           
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
