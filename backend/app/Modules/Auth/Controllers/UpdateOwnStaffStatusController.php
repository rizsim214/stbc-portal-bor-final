<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\UpdateOwnStaffStatusAction;
use App\Modules\Auth\Requests\UpdateStaffStatusRequest;
use Illuminate\Http\JsonResponse;

class UpdateOwnStaffStatusController extends Controller
{
    public function __invoke(UpdateStaffStatusRequest $request, UpdateOwnStaffStatusAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'Staff status updated successfully.',
            'data' => $action->execute($request->toDTO()),
        ]);
    }
}

