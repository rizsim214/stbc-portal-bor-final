<?php

namespace App\Modules\Appointments\Controllers;

use App\Modules\Appointments\Actions\CreateGuestBookAppointment;
use App\Modules\Appointments\Requests\GuestBookAppointmentRequest;
use Illuminate\Http\JsonResponse;

class GuestAppointmentController extends Controller
{
    public function __invoke(
        GuestBookAppointmentRequest $request,
        CreateGuestBookAppointment $action
    ): JsonResponse {
        return response()->json(
            $action->execute($request->toDTO()),
            201
        );
    }
}
