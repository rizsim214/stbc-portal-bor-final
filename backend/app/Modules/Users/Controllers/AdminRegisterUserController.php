<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Actions\AdminRegisterUserAction;
use App\Modules\Users\Requests\AdminRegisterUserRequest;
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


