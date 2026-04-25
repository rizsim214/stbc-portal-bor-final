<?php

namespace App\Modules\Auth\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Auth\DTOs\AssignRoleDTO;
use App\Modules\Auth\Enums\StaffStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignRoleAction
{
    private const MEDICAL_ROLES = ['doctor', 'radiologist', 'lab_technologist', 'staff'];

    public function execute(AssignRoleDTO $dto): User
    {
        $user = User::query()->find($dto->userId);

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$dto->userId]);
        }

        $role = Role::query()->findOrFail($dto->roleId);

        $user->role_id = $role->id;

        if (!in_array($role->name, self::MEDICAL_ROLES, true)) {
            $user->staff_status = null;
        } elseif ($user->staff_status === null) {
            $user->staff_status = StaffStatus::AVAILABLE->value;
        }

        $user->save();

        return $user->load('role');
    }
}
