<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\LogoutAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __invoke(Request $request, LogoutAction $action): JsonResponse
    {
        $action->execute($request->user());

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
