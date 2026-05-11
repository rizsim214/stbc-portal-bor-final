<?php

namespace App\Modules\LabResults\Services;

use Carbon\CarbonInterface;
use App\Modules\LabResults\Exceptions\LabResultStorageConfigurationException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class LabResultFileUrlService
{
    /**
     * @return array{url: string, headers: array<string, string>}
     */
    public function createTemporaryUploadUrl(string $fileKey, string $contentType, CarbonInterface $expiresAt): array
    {
        $disk = $this->adapterDisk();

        return $disk->temporaryUploadUrl($fileKey, $expiresAt, [
            'ContentType' => $contentType,
        ]);
    }

    public function createTemporaryDownloadUrl(string $fileKey, CarbonInterface $expiresAt, ?string $downloadName = null): string
    {
        $disk = $this->adapterDisk();
        $options = [];

        if ($downloadName !== null && $downloadName !== '') {
            $options['ResponseContentDisposition'] = 'inline; filename="' . $downloadName . '"';
        }

        return $disk->temporaryUrl($fileKey, $expiresAt, $options);
    }

    private function diskName(): string
    {
        return (string) config('lab_results.storage_disk', config('filesystems.default'));
    }

    private function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    private function adapterDisk(): FilesystemAdapter
    {
        $disk = $this->disk();

        if (!$disk instanceof FilesystemAdapter) {
            throw new LabResultStorageConfigurationException();
        }

        return $disk;
    }
}
