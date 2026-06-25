<?php

namespace App\Modules\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:assigned,checkup_ongoing,awaiting_result,releasing_lab_result,completed',
            ],
        ];
    }

    public function status(): string
    {
        return (string) $this->string('status');
    }
}
