<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Actions\UpdateAppointmentStatusAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowAppointmentController extends Controller
{
    public function __invoke(Request $request, Appointment $appointment): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin() && (int) $appointment->user_id !== (int) $user->id) {
            abort(403);
        }

        return response()->json([
            'data' => [
                ...$appointment->load([
                    'user:id,name,email',
                    'type:id,name,description',
                    'resources:id,name,type',
                    'labResult:id,appointment_id,file_path,released_at',
                ])->toArray(),
                'allowed_next_statuses' => UpdateAppointmentStatusAction::allowedNextStatuses(
                    (string) $appointment->status,
                ),
            ],
        ]);
    }
}
