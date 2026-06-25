<?php

namespace App\Modules\Users\Controllers;

use App\Models\Resource;
use App\Modules\Users\Support\StaffResourceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowOwnStaffScheduleController extends Controller
{
    public function __invoke(Request $request, StaffResourceService $staffResourceService): JsonResponse
    {
        $resource = Resource::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'resource_id' => $resource->id,
                'days' => $staffResourceService->getWeeklySchedule($resource),
            ],
        ]);
    }
}
