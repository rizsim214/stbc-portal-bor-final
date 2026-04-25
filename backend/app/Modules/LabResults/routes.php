<?php

use App\Modules\LabResults\Controllers\LabResultController;
use Illuminate\Support\Facades\Route;

Route::prefix('lab-results')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', [LabResultController::class, 'index']);
        Route::post('/', [LabResultController::class, 'store'])
            ->middleware('can:upload-lab-results');
        Route::get('/{labResult}', [LabResultController::class, 'show'])
            ->middleware('can:view-lab-result,labResult');
        Route::patch('/{labResult}/release', [LabResultController::class, 'release'])
            ->middleware('can:release-lab-results');
    });

