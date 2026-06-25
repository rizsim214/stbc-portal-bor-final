<?php

namespace App\Modules\Appointments\Requests;

use App\Modules\Appointments\DTOs\RequestOwnAppointmentDTO;
use Illuminate\Foundation\Http\FormRequest;

class RequestOwnAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_type_id' => ['required', 'integer', 'exists:appointment_types,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDTO(): RequestOwnAppointmentDTO
    {
        return new RequestOwnAppointmentDTO(
            userId: (int) $this->user()->id,
            appointmentTypeId: (int) $this->integer('appointment_type_id'),
            startTime: (string) $this->string('start_time'),
            endTime: (string) $this->string('end_time'),
            notes: $this->filled('notes') ? (string) $this->string('notes') : null,
        );
    }
}
