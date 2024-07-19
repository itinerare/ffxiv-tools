<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('game_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('item_id')->unsigned()->index();
            $table->string('name')->nullable()->default(null);
        });

        Schema::create('universalis_cache', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('item_id')->unsigned()->index();
            $table->string('world');

            $table->integer('min_price_nq')->nullable()->default(null);
            $table->integer('min_price_hq')->nullable()->default(null);
            $table->integer('nq_sale_velocity')->nullable()->default(null);
            $table->integer('hq_sale_velocity')->nullable()->default(null);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('game_items');
        Schema::dropIfExists('universalis_cache');
    }
};
