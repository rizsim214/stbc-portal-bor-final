<?php

namespace App\Modules\LabResults\Controllers;

use App\Models\LabResult;
use App\Models\User;
use App\Modules\LabResults\Actions\CreateLabResultAction;
use App\Modules\LabResults\Actions\ListLabResultsAction;
use App\Modules\LabResults\Actions\ReleaseLabResultAction;
use App\Modules\LabResults\Requests\ReleaseLabResultRequest;
use App\Modules\LabResults\Requests\StoreLabResultRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabResultController extends Controller
{
    public function index(Request $request, ListLabResultsAction $action): JsonResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return response()->json([
            'data' => $action->execute($user),
        ]);
    }

    public function store(StoreLabResultRequest $request, CreateLabResultAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'Lab result created successfully.',
            'data' => $action->execute($request->toDTO()),
        ], 201);
    }

    public function show(LabResult $labResult): JsonResponse
    {
        return response()->json([
            'data' => $labResult->load('appointment.user'),
        ]);
    }

    public function release(
        ReleaseLabResultRequest $request,
        LabResult $labResult,
        ReleaseLabResultAction $action
    ): JsonResponse {
        return response()->json([
            'message' => 'Lab result released successfully.',
            'data' => $action->execute($request->toDTO($labResult)),
        ]);
    }
}
