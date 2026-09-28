<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MushroomController;

Route::get('/', [MushroomController::class, 'index'])->name('dashboard');

Route::prefix('api')->group(function () {
    Route::get('/mushroom-data', [MushroomController::class, 'getData']);
    Route::post('/actuators', [MushroomController::class, 'updateActuators']);
    Route::post('/simulate', [MushroomController::class, 'simulate']);
    Route::post('/sensor', [MushroomController::class, 'pushSensorData']);
});
