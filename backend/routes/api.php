<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (): JsonResponse {
    return response()->json([
        'status' => 'ok',
        'service' => 'backend',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Load module routes dynamically
foreach (glob(app_path('Modules/*/routes.php')) as $routeFile) {
    require $routeFile;
}
