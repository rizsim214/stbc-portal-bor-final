<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Actions\AssignAppointmentResourceAction;
use App\Modules\Appointments\Requests\AssignAppointmentResourceRequest;
use Illuminate\Http\JsonResponse;

class AssignAppointmentResourceController extends Controller
{
    public function __invoke(
        AssignAppointmentResourceRequest $request,
        Appointment $appointment,
        AssignAppointmentResourceAction $action
    ): JsonResponse {
        return response()->json([
            'message' => 'Assigned staff updated successfully.',
            'data' => $action->execute($appointment, $request->resourceId()),
        ]);
    }
}
