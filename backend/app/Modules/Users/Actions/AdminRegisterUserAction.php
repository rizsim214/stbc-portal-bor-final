<?php

namespace App\Modules\Users\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Users\DTOs\AdminRegisterUserDTO;
use App\Modules\Users\Support\StaffResourceService;
use Illuminate\Support\Facades\DB;

class AdminRegisterUserAction
{
    public function __construct(
        private readonly StaffResourceService $staffResourceService,
    ) {
    }

    public function execute(AdminRegisterUserDTO $dto): User
    {
        $role = Role::query()->findOrFail($dto->roleId);

        $user = DB::transaction(function () use ($dto, $role): User {
            $user = User::query()->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $dto->password,
                'role_id' => $role->id,
                'sub_role' => $role->name === 'staff' ? $dto->subRole : null,
                'account_status' => 'active',
            ]);

            if ($role->name === 'staff') {
                $this->staffResourceService->syncForStaffUser($user);
            }

            return $user;
        });

        return $user->load('role');
    }
}
