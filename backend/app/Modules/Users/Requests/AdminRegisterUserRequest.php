<?php

namespace App\Modules\Users\Requests;

use App\Models\Role;
use App\Modules\Users\DTOs\AdminRegisterUserDTO;
use App\Modules\Users\Enums\StaffStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminRegisterUserRequest extends FormRequest
{
    private const MEDICAL_ROLES = ['doctor', 'radiologist', 'lab_technologist', 'staff'];

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
            'staff_status' => ['nullable', 'string', 'in:'.implode(',', StaffStatus::values())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $roleId = (int) $this->integer('role_id');

            if ($roleId === 0) {
                return;
            }

            $roleName = (string) Role::query()->whereKey($roleId)->value('name');
            $staffStatus = $this->filled('staff_status') ? (string) $this->string('staff_status') : null;

            if ($staffStatus !== null && !\in_array($roleName, self::MEDICAL_ROLES, true)) {
                $validator->errors()->add('staff_status', 'Staff status can only be set for medical staff roles.');
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
            staffStatus: $this->filled('staff_status') ? (string) $this->string('staff_status') : null,
        );
    }
}

