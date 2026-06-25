<?php

namespace App\Modules\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAssignableResourcesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ];
    }

    public function appointmentId(): ?int
    {
        $appointmentId = $this->integer('appointment_id');

        return $appointmentId > 0 ? (int) $appointmentId : null;
    }
}
