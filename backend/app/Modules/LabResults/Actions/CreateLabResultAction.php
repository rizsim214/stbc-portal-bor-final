<?php

namespace App\Modules\LabResults\Actions;

use App\Models\Appointment;
use App\Models\LabResult;
use App\Modules\LabResults\DTOs\StoreLabResultDTO;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Database\QueryException;

class CreateLabResultAction
{
    public function execute(StoreLabResultDTO $dto): LabResult
    {
        $appointment = Appointment::query()->findOrFail($dto->appointmentId);

        try {
            $labResult = LabResult::query()->create([
                'appointment_id' => $appointment->id,
                'file_path' => $dto->fileKey,
                'result_data' => $dto->resultData,
                'released_at' => $dto->releasedAt,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw new UnprocessableEntityApiException(
                    message: 'A lab result already exists for this appointment.',
                    errorCode: 'LAB_RESULT_ALREADY_EXISTS',
                );
            }

            throw $e;
        }

        return $labResult->load('appointment.user');
    }
}
