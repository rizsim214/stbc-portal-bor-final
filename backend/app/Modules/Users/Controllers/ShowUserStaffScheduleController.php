<?php

namespace App\Modules\Users\Controllers;

use App\Models\User;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use App\Modules\Users\Support\StaffResourceService;
use Illuminate\Http\JsonResponse;

class ShowUserStaffScheduleController extends Controller
{
    public function __invoke(User $user, StaffResourceService $staffResourceService): JsonResponse
    {
        if (!$user->isStaff()) {
            throw new UnprocessableEntityApiException(
                message: 'Only staff accounts have editable staff schedules.',
                errorCode: 'USER_IS_NOT_STAFF',
            );
        }

        $resource = $user->resource()->firstOrFail();

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'sub_role' => $user->sub_role,
                ],
                'resource_id' => $resource->id,
                'days' => $staffResourceService->getWeeklySchedule($resource),
            ],
        ]);
    }
}
