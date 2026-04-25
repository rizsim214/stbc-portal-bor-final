<?php

namespace App\Modules\Users\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;

class ListRolesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => Role::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}


