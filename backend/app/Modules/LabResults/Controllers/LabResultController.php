<?php

namespace App\Modules\LabResults\Controllers;

use App\Models\LabResult;
use App\Models\User;
use App\Modules\Appointments\Controllers\Concerns\FormatsPaginatedResponse;
use App\Modules\LabResults\Actions\CreateLabResultAction;
use App\Modules\LabResults\Actions\GenerateLabResultFileUrlAction;
use App\Modules\LabResults\Actions\GenerateLabResultUploadUrlAction;
use App\Modules\LabResults\Actions\ListLabResultsAction;
use App\Modules\LabResults\Actions\ReleaseLabResultAction;
use App\Modules\LabResults\Requests\GenerateLabResultUploadUrlRequest;
use App\Modules\LabResults\Requests\ListLabResultsRequest;
use App\Modules\LabResults\Requests\ReleaseLabResultRequest;
use App\Modules\LabResults\Requests\StoreLabResultRequest;
use Illuminate\Http\JsonResponse;

class LabResultController extends Controller
{
    use FormatsPaginatedResponse;

    public function index(ListLabResultsRequest $request, ListLabResultsAction $action): JsonResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        return response()->json([
            ...$this->paginatedResponse($action->execute($user, $request->perPage())),
        ]);
    }

    public function store(StoreLabResultRequest $request, CreateLabResultAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'Lab result created successfully.',
            'data' => $action->execute($request->toDTO()),
        ], 201);
    }

    public function generateUploadUrl(
        GenerateLabResultUploadUrlRequest $request,
        GenerateLabResultUploadUrlAction $action
    ): JsonResponse {
        return response()->json([
            'message' => 'Upload URL generated successfully.',
            'data' => $action->execute($request->toDTO()),
        ]);
    }

    public function show(LabResult $labResult): JsonResponse
    {
        return response()->json([
            'data' => $labResult->load('appointment.user'),
        ]);
    }

    public function fileUrl(LabResult $labResult, GenerateLabResultFileUrlAction $action): JsonResponse
    {
        return response()->json([
            'message' => 'Download URL generated successfully.',
            'data' => $action->execute($labResult),
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
