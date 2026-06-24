<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnStaffAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
        ];
    }

    public function isAvailable(): bool
    {
        return (bool) $this->boolean('is_available');
    }
}
