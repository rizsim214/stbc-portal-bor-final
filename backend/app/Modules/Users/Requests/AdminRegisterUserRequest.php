<?php

namespace App\Modules\Users\Requests;

use App\Modules\Users\DTOs\AdminRegisterUserDTO;
use Illuminate\Foundation\Http\FormRequest;

class AdminRegisterUserRequest extends FormRequest
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
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }

    public function toDTO(): AdminRegisterUserDTO
    {
        return new AdminRegisterUserDTO(
            name: (string) $this->string('name'),
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
            roleId: (int) $this->integer('role_id'),
        );
    }
}
