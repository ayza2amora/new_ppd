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
        Schema::create('client_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Reference to the user who performed the action
            $table->string('action',50); // Action description (e.g., 'added', 'edited')
            $table->string('type',50); // Either 'allocation' or 'utilization'
            $table->unsignedBigInteger('record_id'); // The ID of the allocation or utilization that was edited
            $table->timestamps(); // To store when the action happened
    
            // Add foreign key constraint for user_id if you have a users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_logs');
    }
};
