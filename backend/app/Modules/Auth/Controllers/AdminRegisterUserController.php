<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\AdminRegisterUserAction;
use App\Modules\Auth\Requests\AdminRegisterUserRequest;
use Illuminate\Http\JsonResponse;

class AdminRegisterUserController extends Controller
{
    public function __invoke(AdminRegisterUserRequest $request, AdminRegisterUserAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'User created successfully.',
            'data' => $action->execute($request->toDTO()),
        ], 201);
    }
}

