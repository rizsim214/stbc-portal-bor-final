<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Appointments\Controllers\AppointmentController;

Route::prefix('appointments')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('/availability', [AppointmentController::class, 'availability']);
    });
