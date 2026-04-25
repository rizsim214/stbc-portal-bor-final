<?php

namespace App\Modules\Appointments\Controllers;

use App\Modules\Appointments\Actions\CreateAppointment;
use App\Modules\Appointments\Requests\AvailabilityRequest;
use App\Modules\Appointments\Requests\StoreAppointmentRequest;
use App\Modules\Appointments\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request, CreateAppointment $action): JsonResponse
    {
        return response()->json($action->execute($request->toDTO()), 201);
    }

    public function availability(AvailabilityRequest $request, AvailabilityService $service): JsonResponse
    {
        return response()->json($service->getAvailableSlots($request->toDTO()));
    }
}
