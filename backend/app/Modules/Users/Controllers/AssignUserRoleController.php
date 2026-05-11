<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Actions\AssignRoleAction;
use App\Modules\Users\Requests\AssignRoleRequest;
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


