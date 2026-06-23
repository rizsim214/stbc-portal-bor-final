<?php

namespace App\Modules\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAppointmentAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'appointment_type_id' => ['nullable', 'integer', 'exists:appointment_types,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ];
    }
}
