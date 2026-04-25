<?php

namespace App\Modules\LabResults\Actions;

use App\Models\LabResult;
use App\Modules\LabResults\DTOs\ReleaseLabResultDTO;

class ReleaseLabResultAction
{
    public function execute(ReleaseLabResultDTO $dto): LabResult
    {
        $labResult = LabResult::query()->findOrFail($dto->labResultId);
        $labResult->released_at = $dto->releasedAt ?? now();
        $labResult->save();

        return $labResult->load('appointment.user');
    }
}

