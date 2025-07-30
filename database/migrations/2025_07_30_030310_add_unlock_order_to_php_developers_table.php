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
        Schema::table('php_developers', function (Blueprint $table) {
            $table->integer('unlock_order')->default(0); // 0 = unlocked by default, 1-3 = progression order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('php_developers', function (Blueprint $table) {
            $table->dropColumn('unlock_order');
        });
    }
};
