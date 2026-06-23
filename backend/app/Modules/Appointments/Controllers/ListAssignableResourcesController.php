<?php

namespace App\Modules\Appointments\Controllers;

use App\Models\Resource;
use Illuminate\Http\JsonResponse;

class ListAssignableResourcesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $resources = Resource::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        return response()->json([
            'data' => $resources,
        ]);
    }
}
