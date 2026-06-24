<?php

namespace App\Modules\Users\Controllers;

use App\Models\Resource;
use App\Modules\Users\Requests\UpdateOwnStaffAvailabilityRequest;
use Illuminate\Http\JsonResponse;

class UpdateOwnStaffAvailabilityController extends Controller
{
    public function __invoke(UpdateOwnStaffAvailabilityRequest $request): JsonResponse
    {
        $user = $request->user();

        $resource = Resource::query()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $resource->is_available = $request->isAvailable();
        $resource->save();

        return response()->json([
            'message' => 'Staff availability updated successfully.',
            'data' => [
                'is_available' => (bool) $resource->is_available,
                'resource_id' => $resource->id,
            ],
        ]);
    }
}
