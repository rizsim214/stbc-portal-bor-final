<?php

namespace App\Modules\Appointments\Controllers;

use App\Modules\Appointments\Requests\ListAppointmentAvailabilityRequest;
use App\Modules\Appointments\Services\AppointmentScheduleService;
use Illuminate\Http\JsonResponse;

class ListAppointmentAvailabilityController extends Controller
{
    public function __invoke(
        ListAppointmentAvailabilityRequest $request,
        AppointmentScheduleService $appointmentScheduleService
    ): JsonResponse {
        return response()->json([
            'date' => (string) $request->string('date'),
            'appointment_type_id' => $request->integer('appointment_type_id') ?: null,
            'slots' => $appointmentScheduleService->getAvailableSlots(
                (string) $request->string('date'),
                $request->integer('appointment_id') ?: null,
            ),
        ]);
    }
}
