<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Actions\ForgotPasswordAction;
use App\Modules\Auth\Requests\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $message = $action->execute($request->toDTO());

        return response()->json([
            'message' => $message,
        ]);
    }
}

