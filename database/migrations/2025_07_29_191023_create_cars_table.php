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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand'); // Lamborghini, Ferrari, etc.
            $table->string('model');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->integer('speed_rating')->default(50); // 1-100
            $table->integer('acceleration_rating')->default(50); // 1-100
            $table->integer('handling_rating')->default(50); // 1-100
            $table->boolean('is_lambo')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->integer('unlock_level')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
