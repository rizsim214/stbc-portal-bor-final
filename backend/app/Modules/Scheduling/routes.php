<?php

use App\Modules\Scheduling\Controllers\SchedulingController;
use Illuminate\Support\Facades\Route;

Route::prefix('scheduling')->group(function () {
    // Availability is public so guests can browse open schedules before login.
    Route::get('/availability', [SchedulingController::class, 'availability']);
});
