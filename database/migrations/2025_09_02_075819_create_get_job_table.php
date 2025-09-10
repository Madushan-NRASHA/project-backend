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
    Schema::create('get_job', function (Blueprint $table) {
        $table->id();
        $table->string('job_name');
        $table->string('job_catogary'); // note typo consistent with model
        $table->text('description')->nullable();
        $table->unsignedBigInteger('user_id');
        $table->string('location')->nullable();
        $table->string('salary_range')->nullable();
        $table->string('job_type')->nullable();
        $table->timestamps();
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
