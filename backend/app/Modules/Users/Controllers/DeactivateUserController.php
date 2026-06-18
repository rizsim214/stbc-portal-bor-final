<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Actions\DeactivateUserAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeactivateUserController extends Controller
{
    public function __invoke(Request $request, int $user, DeactivateUserAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'User status updated successfully.',
            'data' => $action->execute($user),
        ]);
    }
}
