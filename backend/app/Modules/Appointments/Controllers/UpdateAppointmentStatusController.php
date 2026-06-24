<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Actions\UpdateAppointmentStatusAction;
use App\Modules\Appointments\Requests\UpdateAppointmentStatusRequest;
use Illuminate\Http\JsonResponse;

class UpdateAppointmentStatusController extends Controller
{
    public function __invoke(
        UpdateAppointmentStatusRequest $request,
        Appointment $appointment,
        UpdateAppointmentStatusAction $action,
    ): JsonResponse {
        return response()->json([
            'message' => 'Appointment status updated successfully.',
            'data' => $action->execute($appointment, $request->status()),
        ]);
    }
}
