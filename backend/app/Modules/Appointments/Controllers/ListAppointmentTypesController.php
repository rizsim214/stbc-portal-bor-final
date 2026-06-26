<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\AppointmentType;
use Illuminate\Http\JsonResponse;

class ListAppointmentTypesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appointmentTypes = AppointmentType::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json([
            'data' => $appointmentTypes,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
