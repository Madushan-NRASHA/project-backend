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
        Schema::create('msgs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');       // Related Job ID
            $table->unsignedBigInteger('sender_id');    // Message sender ID
            $table->unsignedBigInteger('receiver_id');  // Message receiver ID
            $table->text('message');                    // Message content
            $table->json('extra_data')->nullable();     // Optional extra data (JSON)
            $table->timestamps();

            // Foreign keys (optional, if jobs and users tables exist)
           $table->foreign('job_id')->references('id')->on('get_job')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msgs');
    }
};
