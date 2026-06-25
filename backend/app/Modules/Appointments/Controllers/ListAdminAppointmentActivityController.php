<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Requests\ListAdminAppointmentActivityRequest;
use Illuminate\Http\JsonResponse;

class ListAdminAppointmentActivityController extends Controller
{
    public function __invoke(ListAdminAppointmentActivityRequest $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->with(['user:id,name,email,created_at', 'type:id,name'])
            ->orderByDesc('created_at')
            ->limit($request->limit())
            ->get();

        return response()->json([
            'data' => $appointments,
        ]);
    }
}
