<?php

use App\Modules\Users\Controllers\AdminRegisterUserController;
use App\Modules\Users\Controllers\AssignUserRoleController;
use App\Modules\Users\Controllers\ListRolesController;
use App\Modules\Users\Controllers\UpdateOwnStaffStatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', ListRolesController::class)
        ->middleware('can:manage-roles');

    Route::post('/users', AdminRegisterUserController::class)
        ->middleware('can:manage-users');

    Route::patch('/users/{user}/role', AssignUserRoleController::class)
        ->middleware('can:manage-users');

    Route::patch('/users/me/staff-status', UpdateOwnStaffStatusController::class)
        ->middleware('can:update-own-staff-status');
});

