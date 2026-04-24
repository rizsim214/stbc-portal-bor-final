<?php

use App\Modules\Appointments\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('appointments')->group(function () {
    Route::get('/test', function () {
        return response()->json([
            'message' => 'Appointments module working'
        ]);
    });
});

Route::prefix('appointments')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::post('/', [AppointmentController::class, 'store']);
    });
