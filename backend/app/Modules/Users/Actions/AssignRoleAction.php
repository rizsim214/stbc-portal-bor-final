<?php

namespace App\Modules\Users\Actions;

use App\Models\Role;
use App\Models\Resource;
use App\Models\User;
use App\Modules\Users\DTOs\AssignRoleDTO;
use App\Modules\Users\Support\StaffResourceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignRoleAction
{
    public function __construct(
        private readonly StaffResourceService $staffResourceService,
    ) {
    }

    public function execute(AssignRoleDTO $dto): User
    {
        $user = User::query()->find($dto->userId);

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$dto->userId]);
        }

        $role = Role::query()->findOrFail($dto->roleId);

        $user->role_id = $role->id;

        $user->save();

        if ($role->name === 'staff') {
            $this->staffResourceService->syncForStaffUser($user);
        } else {
            Resource::query()
                ->where('user_id', $user->id)
                ->update(['is_active' => false]);
        }

        return $user->load('role');
    }
}
