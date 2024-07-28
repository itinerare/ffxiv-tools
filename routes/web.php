<?php

use App\Http\Controllers\CraftingController;
use App\Http\Controllers\DiademController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LevelingController;
use Illuminate\Support\Facades\Route;

Route::controller(IndexController::class)->group(function () {
    Route::get('/', 'getIndex');
});

Route::controller(DiademController::class)->group(function () {
    Route::get('diadem', 'getDiadem');
});

Route::controller(LevelingController::class)->group(function () {
    Route::get('leveling', 'getLeveling');
});

Route::controller(CraftingController::class)->group(function () {
    Route::get('crafting', 'getCalculator');
});
