<?php

namespace App\Modules\Users\Requests;

use App\Modules\Users\DTOs\UpdatePatientByAdminDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientByAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function toDTO(): UpdatePatientByAdminDTO
    {
        return new UpdatePatientByAdminDTO(
            name: trim((string) $this->string('name')),
            password: $this->filled('password') ? (string) $this->string('password') : null,
        );
    }
}
