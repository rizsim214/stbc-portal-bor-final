<?php

namespace App\Modules\Auth\Requests;

use App\Modules\Auth\DTOs\LoginDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): LoginDTO
    {
        return new LoginDTO(
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
            deviceName: $this->filled('device_name') ? (string) $this->string('device_name') : 'web',
        );
    }
}
