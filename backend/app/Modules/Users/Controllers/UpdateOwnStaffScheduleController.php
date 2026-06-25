<?php

namespace App\Modules\Users\Controllers;

use App\Models\Resource;
use App\Modules\Users\Requests\UpdateStaffScheduleRequest;
use App\Modules\Users\Support\StaffResourceService;
use Illuminate\Http\JsonResponse;

class UpdateOwnStaffScheduleController extends Controller
{
    public function __invoke(
        UpdateStaffScheduleRequest $request,
        StaffResourceService $staffResourceService
    ): JsonResponse {
        $resource = Resource::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $staffResourceService->replaceWeeklySchedule($resource, $request->days());

        return response()->json([
            'message' => 'Staff schedule updated successfully.',
            'data' => [
                'resource_id' => $resource->id,
                'days' => $staffResourceService->getWeeklySchedule($resource),
            ],
        ]);
    }
}
