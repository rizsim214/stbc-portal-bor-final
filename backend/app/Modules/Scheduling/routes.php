<?php

use App\Modules\Scheduling\Controllers\SchedulingController;
use Illuminate\Support\Facades\Route;

Route::prefix('scheduling')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/availability', [SchedulingController::class, 'availability']);
    });

