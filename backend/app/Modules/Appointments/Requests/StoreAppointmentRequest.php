<?php

namespace App\Modules\Appointments\Requests;

use App\Modules\Appointments\DTOs\StoreAppointmentDTO;
use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_type_id' => ['required', 'exists:appointment_types,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'resource_ids' => ['required', 'array'],
            'resource_ids.*' => ['exists:resources,id'],
        ];
    }

    public function toDTO(): StoreAppointmentDTO
    {
        return new StoreAppointmentDTO(
            userId: (int) $this->user()->id,
            appointmentTypeId: (int) $this->integer('appointment_type_id'),
            startTime: (string) $this->string('start_time'),
            endTime: (string) $this->string('end_time'),
            resourceIds: array_map(static fn (mixed $id): int => (int) $id, $this->array('resource_ids')),
        );
    }
}
