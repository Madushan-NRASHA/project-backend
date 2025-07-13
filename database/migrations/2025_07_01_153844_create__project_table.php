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
       Schema::create('projects', function (Blueprint $table) {
    $table->id(); // id
    $table->unsignedBigInteger('portfolio_id'); // foreign key
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('photo')->nullable(); // assuming a URL or file path
    $table->timestamps();

    $table->foreign('portfolio_id')->references('id')->on('portfolios')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
