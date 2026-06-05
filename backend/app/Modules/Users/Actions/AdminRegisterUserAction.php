<?php

namespace App\Modules\Users\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Users\DTOs\AdminRegisterUserDTO;

class AdminRegisterUserAction
{
    public function execute(AdminRegisterUserDTO $dto): User
    {
        $role = Role::query()->findOrFail($dto->roleId);

        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'role_id' => $role->id,
        ]);

        return $user->load('role');
    }
}
