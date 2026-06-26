<?php

namespace App\Modules\Users\Controllers;

use App\Models\User;
use App\Modules\Users\Actions\UpdatePatientByAdminAction;
use App\Modules\Users\Requests\UpdatePatientByAdminRequest;
use Illuminate\Http\JsonResponse;

class UpdatePatientByAdminController extends Controller
{
    public function __invoke(
        UpdatePatientByAdminRequest $request,
        User $user,
        UpdatePatientByAdminAction $action,
    ): JsonResponse {
        return response()->json([
            'message' => 'Patient updated successfully.',
            'data' => $action->execute($user, $request->toDTO()),
        ]);
    }
}
