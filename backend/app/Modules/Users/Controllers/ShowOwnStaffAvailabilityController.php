<?php

namespace App\Modules\Users\Controllers;

use App\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowOwnStaffAvailabilityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $resource = Resource::query()
            ->where('user_id', $user->id)
            ->first();

        return response()->json([
            'data' => [
                'is_available' => (bool) ($resource?->is_available ?? false),
                'resource_id' => $resource?->id,
            ],
        ]);
    }
}
