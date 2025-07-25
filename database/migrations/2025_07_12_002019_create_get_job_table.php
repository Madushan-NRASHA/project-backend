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
       Schema::create('get_job', function (Blueprint $table) {
    $table->id();
    $table->string("job_name");
    $table->unsignedBigInteger('user_id'); // Add this line
    $table->timestamps();

    // Foreign key constraint
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('get_job');
    }
};
