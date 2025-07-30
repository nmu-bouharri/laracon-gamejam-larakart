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
        Schema::create('race_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('php_developer_id')->constrained('php_developers');
            $table->foreignId('car_id')->constrained('cars');
            $table->integer('position')->nullable(); // Final race position
            $table->integer('current_lap')->default(0);
            $table->json('lap_times')->nullable(); // Array of lap completion times
            $table->json('position_data')->nullable(); // Real-time position tracking
            $table->boolean('finished')->default(false);
            $table->timestamp('finish_time')->nullable();
            $table->timestamps();
            
            $table->unique(['race_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_participants');
    }
};
