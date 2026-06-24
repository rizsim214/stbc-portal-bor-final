<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Appointments\Controllers\AssignAppointmentResourceController;
use App\Modules\Appointments\Controllers\AppointmentController;
use App\Modules\Appointments\Controllers\GuestAppointmentController;
use App\Modules\Appointments\Controllers\ListAppointmentAvailabilityController;
use App\Modules\Appointments\Controllers\ListAppointmentCalendarController;
use App\Modules\Appointments\Controllers\ListAllAppointmentsController;
use App\Modules\Appointments\Controllers\ListAssignableResourcesController;
use App\Modules\Appointments\Controllers\ListMyAppointmentsController;
use App\Modules\Appointments\Controllers\ListAppointmentTypesController;
use App\Modules\Appointments\Controllers\ShowAppointmentController;
use App\Modules\Appointments\Controllers\RequestOwnAppointmentController;
use App\Modules\Appointments\Controllers\UpdateAppointmentController;

Route::prefix('appointments')->group(function () {
    Route::get('/types', ListAppointmentTypesController::class);
    Route::get('/availability', ListAppointmentAvailabilityController::class);
    Route::get('/calendar', ListAppointmentCalendarController::class);
    Route::post('/guest', GuestAppointmentController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/resources', ListAssignableResourcesController::class)
            ->middleware('can:manage-users');
        Route::get('/mine', ListMyAppointmentsController::class);
        Route::get('/admin-list', ListAllAppointmentsController::class)
            ->middleware('can:manage-users');
        Route::get('/{appointment}', ShowAppointmentController::class);
        Route::patch('/{appointment}', UpdateAppointmentController::class);
        Route::patch('/{appointment}/assignment', AssignAppointmentResourceController::class)
            ->middleware('can:manage-users');
        Route::post('/', [AppointmentController::class, 'store'])
            ->middleware('can:book-appointments');
        Route::post('/request', RequestOwnAppointmentController::class)
            ->middleware('role:patient,user');
    });
});
