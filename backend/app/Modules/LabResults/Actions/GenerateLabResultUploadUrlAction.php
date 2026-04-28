<?php

namespace App\Modules\LabResults\Actions;

use App\Models\Appointment;
use App\Models\LabResult;
use App\Modules\LabResults\DTOs\GenerateLabResultUploadUrlDTO;
use App\Modules\LabResults\Services\LabResultFileUrlService;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Support\Str;

class GenerateLabResultUploadUrlAction
{
    public function __construct(
        private readonly LabResultFileUrlService $fileUrlService,
    ) {
    }

    /**
     * @return array{
     *   upload_url: string,
     *   headers: array<string, string>,
     *   file_key: string,
     *   expires_at: string
     * }
     */
    public function execute(GenerateLabResultUploadUrlDTO $dto): array
    {
        $appointment = Appointment::query()->findOrFail($dto->appointmentId);
        $existing = LabResult::query()->where('appointment_id', $appointment->id)->exists();

        if ($existing) {
            throw new UnprocessableEntityApiException(
                message: 'A lab result already exists for this appointment.',
                errorCode: 'LAB_RESULT_ALREADY_EXISTS',
            );
        }

        $fileKey = $this->buildFileKey(
            appointmentId: $appointment->id,
            fileName: $dto->fileName,
        );
        $expiresAt = now()->addMinutes((int) config('lab_results.upload_url_ttl_minutes', 10));

        $signedUpload = $this->fileUrlService->createTemporaryUploadUrl(
            fileKey: $fileKey,
            contentType: $dto->contentType,
            expiresAt: $expiresAt,
        );

        return [
            'upload_url' => $signedUpload['url'],
            'headers' => $signedUpload['headers'],
            'file_key' => $fileKey,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    private function buildFileKey(int $appointmentId, string $fileName): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $suffix = $extension !== '' ? '.'.$extension : '';

        return 'lab-results/'.$appointmentId.'/'.Str::uuid()->toString().$suffix;
    }
}
