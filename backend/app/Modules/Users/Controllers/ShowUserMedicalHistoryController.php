<?php

namespace App\Modules\Users\Controllers;

use App\Models\User;
use App\Modules\Users\Actions\ShowUserMedicalHistoryAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowUserMedicalHistoryController extends Controller
{
    public function __invoke(
        Request $request,
        User $user,
        ShowUserMedicalHistoryAction $action
    ): JsonResponse {
        $authUser = $request->user();

        abort_unless($authUser instanceof User, 401);
        abort_unless($authUser->can('view-medical-history', $user), 403);

        return response()->json([
            'data' => $action->execute($user),
        ]);
    }
}
