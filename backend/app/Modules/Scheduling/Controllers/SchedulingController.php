<?php

namespace App\Modules\Scheduling\Controllers;

use App\Modules\Scheduling\Requests\SchedulingAvailabilityRequest;
use App\Modules\Scheduling\Services\SchedulingService;
use Illuminate\Http\JsonResponse;

class SchedulingController extends Controller
{
    public function availability(
        SchedulingAvailabilityRequest $request,
        SchedulingService $service
    ): JsonResponse {
        return response()->json($service->getAvailableSlots($request->toDTO()));
    }
}

