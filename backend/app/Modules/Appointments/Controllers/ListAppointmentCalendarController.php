<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Requests\ListAppointmentCalendarRequest;
use Illuminate\Http\JsonResponse;

class ListAppointmentCalendarController extends Controller
{
    public function __invoke(ListAppointmentCalendarRequest $request): JsonResponse
    {
        $start = (string) $request->string('start');
        $end = (string) $request->string('end');

        $appointments = Appointment::query()
            ->with(['type:id,name'])
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'data' => $appointments->map(static function (Appointment $appointment): array {
                return [
                    'id' => $appointment->id,
                    'appointment_type_id' => $appointment->appointment_type_id,
                    'start_time' => $appointment->start_time,
                    'end_time' => $appointment->end_time,
                    'status' => $appointment->status,
                    'type' => $appointment->type
                        ? [
                            'id' => $appointment->type->id,
                            'name' => $appointment->type->name,
                        ]
                        : null,
                ];
            })->values()->all(),
        ]);
    }
}
