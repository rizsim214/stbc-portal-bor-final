<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Appointment;
use App\Models\Resource;
use App\Modules\Appointments\Requests\ListAssignableResourcesRequest;
use App\Modules\Scheduling\Services\SchedulingService;
use Illuminate\Http\JsonResponse;

class ListAssignableResourcesController extends Controller
{
    public function __invoke(
        ListAssignableResourcesRequest $request,
        SchedulingService $schedulingService
    ): JsonResponse
    {
        $resources = Resource::query()
            ->where('is_active', true)
            ->with(['user:id,name,sub_role,account_status'])
            ->orderBy('name')
            ->get(['id', 'user_id', 'name', 'type', 'is_available']);

        $resources = $resources->filter(function (Resource $resource): bool {
            if ($resource->user === null) {
                return true;
            }

            return strtolower(trim((string) $resource->user->account_status)) !== 'inactive';
        })->values();

        $resources->transform(function (Resource $resource): Resource {
            if ($resource->user !== null) {
                $resource->name = $resource->user->name;
                $resource->type = $resource->user->sub_role ?: $resource->type;
            }

            return $resource;
        });

        $appointmentId = $request->appointmentId();

        if ($appointmentId !== null) {
            $appointment = Appointment::query()->findOrFail($appointmentId);

            return response()->json([
                'data' => $resources->map(
                    fn(Resource $resource): array => [
                        'id' => $resource->id,
                        'name' => $resource->user?->name ?? $resource->name,
                        'type' => $resource->user?->sub_role ?: $resource->type,
                        'is_available' => (bool) $resource->is_available
                            && $schedulingService->isAvailableExcludingAppointment(
                            [$resource->id],
                            $appointment->start_time,
                            $appointment->end_time,
                            $appointment->id,
                        ),
                    ],
                )->values(),
            ]);
        }

        return response()->json([
            'data' => $resources->map(
                fn(Resource $resource): array => [
                    'id' => $resource->id,
                    'name' => $resource->user?->name ?? $resource->name,
                    'type' => $resource->user?->sub_role ?: $resource->type,
                    'is_available' => (bool) $resource->is_available,
                ],
            )->values(),
        ]);
    }
}
