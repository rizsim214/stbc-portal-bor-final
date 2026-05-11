<?php

namespace App\Modules\LabResults\Actions;

use App\Models\LabResult;
use App\Modules\LabResults\Services\LabResultFileUrlService;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;

class GenerateLabResultFileUrlAction
{
    public function __construct(
        private readonly LabResultFileUrlService $fileUrlService,
    ) {
    }

    /**
     * @return array{download_url: string, expires_at: string}
     */
    public function execute(LabResult $labResult): array
    {
        if (!$labResult->file_path) {
            throw new UnprocessableEntityApiException(
                message: 'Lab result does not have an uploaded file.',
                errorCode: 'LAB_RESULT_FILE_MISSING',
            );
        }

        $expiresAt = now()->addMinutes((int) config('lab_results.download_url_ttl_minutes', 5));
        $extension = pathinfo($labResult->file_path, PATHINFO_EXTENSION);
        $downloadName = 'lab-result-'.$labResult->id.($extension !== '' ? '.'.$extension : '');
        $downloadUrl = $this->fileUrlService->createTemporaryDownloadUrl(
            fileKey: $labResult->file_path,
            expiresAt: $expiresAt,
            downloadName: $downloadName,
        );

        return [
            'download_url' => $downloadUrl,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }
}
