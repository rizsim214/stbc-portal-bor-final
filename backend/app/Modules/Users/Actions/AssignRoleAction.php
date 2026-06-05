<?php

namespace App\Modules\Users\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Users\DTOs\AssignRoleDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignRoleAction
{
    public function execute(AssignRoleDTO $dto): User
    {
        $user = User::query()->find($dto->userId);

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$dto->userId]);
        }

        $role = Role::query()->findOrFail($dto->roleId);

        $user->role_id = $role->id;

        $user->save();

        return $user->load('role');
    }
}
