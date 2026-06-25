<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Requests\UpdateOwnPasswordRequest;
use Illuminate\Http\JsonResponse;

class UpdateOwnPasswordController extends Controller
{
    public function __invoke(UpdateOwnPasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update([
            'password' => $request->newPassword(),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
}
