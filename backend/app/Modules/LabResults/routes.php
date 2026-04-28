<?php

use App\Modules\LabResults\Controllers\LabResultController;
use Illuminate\Support\Facades\Route;

Route::prefix('lab-results')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/', [LabResultController::class, 'index']);
        Route::post('/upload-url', [LabResultController::class, 'generateUploadUrl'])
            ->middleware('can:upload-lab-results');
        Route::post('/', [LabResultController::class, 'store'])
            ->middleware('can:upload-lab-results');
        Route::get('/{labResult}/file-url', [LabResultController::class, 'fileUrl'])
            ->middleware('can:view-lab-result,labResult');
        Route::get('/{labResult}', [LabResultController::class, 'show'])
            ->middleware('can:view-lab-result,labResult');
        Route::patch('/{labResult}/release', [LabResultController::class, 'release'])
            ->middleware('can:release-lab-results');
    });
