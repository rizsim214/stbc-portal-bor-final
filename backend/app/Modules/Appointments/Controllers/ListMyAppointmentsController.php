<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Modules\Appointments\Controllers\Concerns\FormatsPaginatedResponse;
use App\Modules\Appointments\Requests\ListAppointmentsRequest;
use Illuminate\Http\JsonResponse;

class ListMyAppointmentsController extends Controller
{
    use FormatsPaginatedResponse;

    public function __invoke(ListAppointmentsRequest $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->with(['type:id,name', 'resources:id,name,type'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('start_time')
            ->paginate($request->perPage());

        return response()->json($this->paginatedResponse($appointments));
    }
}
