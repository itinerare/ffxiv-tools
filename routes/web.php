<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\DiademController;
use App\Http\Controllers\LevelingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::controller(Controller::class)->group(function () {
    Route::get('/', 'getIndex');
});

Route::controller(DiademController::class)->group(function () {
    Route::get('diadem', 'getDiadem');
});

Route::controller(LevelingController::class)->group(function () {
    Route::get('leveling', 'getLeveling');
});
