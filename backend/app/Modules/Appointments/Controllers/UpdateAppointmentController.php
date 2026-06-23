<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Actions\UpdateAppointmentAction;
use App\Modules\Appointments\Requests\UpdateAppointmentRequest;
use Illuminate\Http\JsonResponse;

class UpdateAppointmentController extends Controller
{
    public function __invoke(
        UpdateAppointmentRequest $request,
        Appointment $appointment,
        UpdateAppointmentAction $action
    ): JsonResponse {
        $user = $request->user();

        if (!$user->isAdmin() && (int) $appointment->user_id !== (int) $user->id) {
            abort(403);
        }

        return response()->json([
            'message' => 'Appointment updated successfully.',
            'data' => $action->execute($appointment, $request->toDTO()),
        ]);
    }
}
