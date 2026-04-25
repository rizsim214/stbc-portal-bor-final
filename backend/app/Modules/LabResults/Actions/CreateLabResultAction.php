<?php

namespace App\Modules\LabResults\Actions;

use App\Models\Appointment;
use App\Models\LabResult;
use App\Modules\LabResults\DTOs\StoreLabResultDTO;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;

class CreateLabResultAction
{
    public function execute(StoreLabResultDTO $dto): LabResult
    {
        $appointment = Appointment::query()->findOrFail($dto->appointmentId);

        $existing = LabResult::query()->where('appointment_id', $appointment->id)->exists();

        if ($existing) {
            throw new UnprocessableEntityApiException(
                message: 'A lab result already exists for this appointment.',
                errorCode: 'LAB_RESULT_ALREADY_EXISTS',
            );
        }

        $labResult = LabResult::query()->create([
            'appointment_id' => $appointment->id,
            'file_path' => $dto->filePath,
            'result_data' => $dto->resultData,
            'released_at' => $dto->releasedAt,
        ]);

        return $labResult->load('appointment.user');
    }
}

