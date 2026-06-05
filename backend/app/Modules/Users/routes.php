<?php

use App\Modules\Users\Controllers\AdminRegisterUserController;
use App\Modules\Users\Controllers\AssignUserRoleController;
use App\Modules\Users\Controllers\ListUsersController;
use App\Modules\Users\Controllers\ListRolesController;
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
});
