<?php

namespace App\Modules\Users\Requests;

use App\Models\Role;
use App\Modules\Users\DTOs\AdminRegisterUserDTO;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'sub_role' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $roleId = (int) $this->integer('role_id');

            if ($roleId <= 0) {
                return;
            }

            $roleName = Role::query()->whereKey($roleId)->value('name');
            $subRole = trim((string) $this->input('sub_role', ''));

            if ($roleName === 'staff' && $subRole === '') {
                $validator->errors()->add('sub_role', 'The sub role field is required when the selected role is staff.');
            }
        });
    }

    public function toDTO(): AdminRegisterUserDTO
    {
        return new AdminRegisterUserDTO(
            name: (string) $this->string('name'),
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
            roleId: (int) $this->integer('role_id'),
            subRole: $this->filled('sub_role') ? trim((string) $this->string('sub_role')) : null,
        );
    }
}
