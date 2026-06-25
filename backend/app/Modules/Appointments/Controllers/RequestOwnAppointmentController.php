<?php

namespace App\Modules\Appointments\Controllers;

use App\Modules\Appointments\Actions\RequestOwnAppointmentAction;
use App\Modules\Appointments\Requests\RequestOwnAppointmentRequest;
use Illuminate\Http\JsonResponse;

class RequestOwnAppointmentController extends Controller
{
    public function __invoke(
        RequestOwnAppointmentRequest $request,
        RequestOwnAppointmentAction $action
    ): JsonResponse {
        return response()->json([
            'message' => 'Appointment request submitted successfully.',
            'data' => $action->execute($request->toDTO()),
        ], 201);
    }
}
