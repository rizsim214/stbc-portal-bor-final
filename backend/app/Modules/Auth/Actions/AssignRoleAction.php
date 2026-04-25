<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\DTOs\AssignRoleDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignRoleAction
{
    public function execute(AssignRoleDTO $dto): User
    {
        $user = User::query()->find($dto->userId);

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$dto->userId]);
        }

        $user->role_id = $dto->roleId;
        $user->save();

        return $user->load('role');
    }
}

