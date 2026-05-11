<?php

namespace App\Modules\Users\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Users\DTOs\AdminRegisterUserDTO;
use App\Modules\Users\Enums\StaffStatus;
use Illuminate\Validation\ValidationException;

class AdminRegisterUserAction
{
    private const MEDICAL_ROLES = ['doctor', 'radiologist', 'lab_technologist', 'staff'];

    public function execute(AdminRegisterUserDTO $dto): User
    {
        $role = Role::query()->findOrFail($dto->roleId);

        if ($dto->staffStatus !== null && !\in_array($role->name, self::MEDICAL_ROLES, true)) {
            throw ValidationException::withMessages([
                'staff_status' => ['Staff status can only be assigned to medical staff roles.'],
            ]);
        }

        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'role_id' => $role->id,
            'staff_status' => $this->resolveStaffStatus($role->name, $dto->staffStatus),
        ]);

        return $user->load('role');
    }

    private function resolveStaffStatus(string $roleName, ?string $staffStatus): ?string
    {
        if (!\in_array($roleName, self::MEDICAL_ROLES, true)) {
            return null;
        }

        return $staffStatus ?? StaffStatus::AVAILABLE->value;
    }
}

