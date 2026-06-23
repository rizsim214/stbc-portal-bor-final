<?php

namespace App\Modules\Appointments\Requests;

use App\Modules\Appointments\DTOs\GuestBookAppointmentDTO;
use Illuminate\Foundation\Http\FormRequest;

class GuestBookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'appointment_type_id' => ['required', 'integer', 'exists:appointment_types,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function toDTO(): GuestBookAppointmentDTO
    {
        return new GuestBookAppointmentDTO(
            name: (string) $this->string('name'),
            email: (string) $this->string('email'),
            appointmentTypeId: (int) $this->integer('appointment_type_id'),
            startTime: (string) $this->string('start_time'),
            endTime: (string) $this->string('end_time'),
            notes: $this->filled('notes') ? (string) $this->string('notes') : null,
        );
    }
}
