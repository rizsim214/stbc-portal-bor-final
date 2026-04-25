<?php

namespace App\Modules\Auth\Requests;

use App\Modules\Auth\DTOs\RegisterDTO;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): RegisterDTO
    {
        return new RegisterDTO(
            name: (string) $this->string('name'),
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
            deviceName: $this->filled('device_name') ? (string) $this->string('device_name') : 'web',
        );
    }
}

