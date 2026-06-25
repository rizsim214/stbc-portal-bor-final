<?php

use App\Modules\Users\Controllers\AdminRegisterUserController;
use App\Modules\Users\Controllers\AssignUserRoleController;
use App\Modules\Users\Controllers\DeactivateUserController;
use App\Modules\Users\Controllers\ListUsersController;
use App\Modules\Users\Controllers\ListRolesController;
use App\Modules\Users\Controllers\ShowOwnStaffScheduleController;
use App\Modules\Users\Controllers\ShowUserStaffScheduleController;
use App\Modules\Users\Controllers\ShowUserMedicalHistoryController;
use App\Modules\Users\Controllers\ShowOwnStaffAvailabilityController;
use App\Modules\Users\Controllers\UpdateOwnStaffAvailabilityController;
use App\Modules\Users\Controllers\UpdateOwnPasswordController;
use App\Modules\Users\Controllers\UpdateOwnStaffScheduleController;
use App\Modules\Users\Controllers\UpdateUserStaffScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/roles', ListRolesController::class)
        ->middleware('can:manage-roles');

    Route::get('/users', ListUsersController::class)
        ->middleware('can:manage-users');

    Route::post('/users', AdminRegisterUserController::class)
        ->middleware('can:manage-users');

    Route::patch('/users/{user}/role', AssignUserRoleController::class)
        ->middleware('can:manage-users');

    Route::patch('/users/{user}/toggle-status', DeactivateUserController::class)
        ->middleware('can:manage-users');

    Route::patch('/users/me/password', UpdateOwnPasswordController::class);

    Route::get('/users/{user}/medical-history', ShowUserMedicalHistoryController::class);
    Route::get('/users/{user}/staff-schedule', ShowUserStaffScheduleController::class)
        ->middleware('can:manage-users');
    Route::patch('/users/{user}/staff-schedule', UpdateUserStaffScheduleController::class)
        ->middleware('can:manage-users');
    Route::get('/staff/availability', ShowOwnStaffAvailabilityController::class)
        ->middleware('role:staff');
    Route::patch('/staff/availability', UpdateOwnStaffAvailabilityController::class)
        ->middleware('role:staff');
    Route::get('/staff/schedule', ShowOwnStaffScheduleController::class)
        ->middleware('role:staff');
    Route::patch('/staff/schedule', UpdateOwnStaffScheduleController::class)
        ->middleware('role:staff');
});
