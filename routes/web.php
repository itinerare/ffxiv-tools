<?php

use App\Http\Controllers\EconomyController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LevelingController;
use Illuminate\Support\Facades\Route;

Route::controller(IndexController::class)->group(function () {
    Route::get('/', 'getIndex');
});

Route::controller(LevelingController::class)->group(function () {
    Route::get('leveling', 'getLeveling');
});

Route::controller(EconomyController::class)->group(function () {
    Route::get('crafting', 'getCraftingCalculator');
    Route::get('gathering', 'getGatheringCalculator');
    Route::get('drops', 'getDropsCalculator');
    Route::get('diadem', 'getDiademCalculator');
});
