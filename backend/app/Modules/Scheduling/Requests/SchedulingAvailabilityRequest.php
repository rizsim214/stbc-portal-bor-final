<?php

namespace App\Modules\Scheduling\Requests;

use App\Modules\Scheduling\DTOs\SchedulingAvailabilityDTO;
use Illuminate\Foundation\Http\FormRequest;

class SchedulingAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'appointment_type_id' => ['required', 'exists:appointment_types,id'],
            'resource_ids' => ['required', 'array', 'min:1'],
            'resource_ids.*' => ['integer', 'exists:resources,id'],
        ];
    }

    public function toDTO(): SchedulingAvailabilityDTO
    {
        return new SchedulingAvailabilityDTO(
            date: (string) $this->string('date'),
            appointmentTypeId: (int) $this->integer('appointment_type_id'),
            resourceIds: array_map(static fn (mixed $id): int => (int) $id, $this->array('resource_ids')),
        );
    }
}

