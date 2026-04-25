<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\AssignRoleAction;
use App\Modules\Auth\Requests\AssignRoleRequest;
use Illuminate\Http\JsonResponse;

class AssignUserRoleController extends Controller
{
    public function __invoke(AssignRoleRequest $request, AssignRoleAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => $action->execute($request->toDTO()),
        ]);
    }
}

