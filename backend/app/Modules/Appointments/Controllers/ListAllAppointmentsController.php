<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Controllers\Concerns\FormatsPaginatedResponse;
use App\Modules\Appointments\Requests\ListAppointmentsRequest;
use Illuminate\Http\JsonResponse;

class ListAllAppointmentsController extends Controller
{
    use FormatsPaginatedResponse;

    public function __invoke(ListAppointmentsRequest $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->with(['user:id,name,email', 'type:id,name', 'resources:id,name,type'])
            ->orderByDesc('start_time')
            ->paginate($request->perPage());

        return response()->json($this->paginatedResponse($appointments));
    }
}
