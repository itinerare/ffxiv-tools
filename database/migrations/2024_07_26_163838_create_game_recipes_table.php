<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('game_recipes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('recipe_id')->unsigned()->index();
            $table->integer('item_id')->unsigned()->index();
            $table->integer('job')->unsigned()->index();

            $table->integer('level');
            $table->integer('rlvl');
            $table->integer('stars');
            $table->integer('yield');

            $table->text('ingredients')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('game_recipes');
    }
};
