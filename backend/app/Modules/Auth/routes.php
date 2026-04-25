<?php

use App\Modules\Auth\Controllers\ForgotPasswordController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\AssignUserRoleController;
use App\Modules\Auth\Controllers\ListRolesController;
use App\Modules\Auth\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', RegisterController::class);
    Route::post('/login', LoginController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);

    Route::get('/reset-password/{token}', function (string $token) {
        return response()->json([
            'message' => 'Use the frontend reset-password page.',
            'token' => $token,
            'email' => request()->query('email'),
        ]);
    })->name('password.reset');
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/roles', ListRolesController::class);
    Route::patch('/users/{user}/role', AssignUserRoleController::class);
});
