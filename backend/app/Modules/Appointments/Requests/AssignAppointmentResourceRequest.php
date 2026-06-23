<?php

namespace App\Modules\Appointments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignAppointmentResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'integer', 'exists:resources,id'],
        ];
    }

    public function resourceId(): int
    {
        return (int) $this->integer('resource_id');
    }
}
