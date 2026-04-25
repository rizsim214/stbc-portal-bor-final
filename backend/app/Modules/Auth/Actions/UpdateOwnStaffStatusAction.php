<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\DTOs\UpdateStaffStatusDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateOwnStaffStatusAction
{
    public function execute(UpdateStaffStatusDTO $dto): User
    {
        $user = User::query()->find($dto->userId);

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$dto->userId]);
        }

        $user->staff_status = $dto->staffStatus;
        $user->save();

        return $user->load('role');
    }
}

