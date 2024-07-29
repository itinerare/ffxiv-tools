<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('game_items', function (Blueprint $table) {
            $table->text('gather_data')->nullable()->default(null);
            $table->boolean('is_mob_drop')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('game_items', function (Blueprint $table) {
            $table->dropColumn('gather_data');
            $table->dropColumn('is_mob_drop');
        });
    }
};
