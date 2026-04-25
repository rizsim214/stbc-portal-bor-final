<?php

namespace App\Modules\Appointments\Requests;

use App\Modules\Appointments\DTOs\AvailabilityDTO;
use Illuminate\Foundation\Http\FormRequest;

class AvailabilityRequest extends FormRequest
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
        ];
    }

    public function toDTO(): AvailabilityDTO
    {
        return new AvailabilityDTO(
            date: (string) $this->string('date'),
            appointmentTypeId: (int) $this->integer('appointment_type_id'),
        );
    }
}

