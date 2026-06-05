<?php

namespace App\Modules\Users\Controllers;

use App\Modules\Users\Actions\ListUsersAction;
use Illuminate\Http\JsonResponse;

class ListUsersController extends Controller
{
    public function __invoke(ListUsersAction $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute(),
        ]);
    }
}
