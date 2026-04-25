<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\LoginAction;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $result = $action->execute($request->toDTO());

        return response()->json([
            'message' => 'Login successful.',
            'data' => $result,
        ]);
    }
}
