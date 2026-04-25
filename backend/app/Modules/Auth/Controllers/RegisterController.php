<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\RegisterAction;
use App\Modules\Auth\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        $result = $action->execute($request->toDTO());

        return response()->json([
            'message' => 'Registration successful.',
            'data' => $result,
        ], 201);
    }
}

