<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Actions\UpdateOwnStaffStatusAction;
use App\Modules\Users\Requests\UpdateStaffStatusRequest;
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


