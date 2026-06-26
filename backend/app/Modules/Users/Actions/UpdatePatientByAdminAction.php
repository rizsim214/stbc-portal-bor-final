<?php

namespace App\Modules\Users\Actions;

use App\Models\User;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use App\Modules\Users\DTOs\UpdatePatientByAdminDTO;

class UpdatePatientByAdminAction
{
    public function execute(User $user, UpdatePatientByAdminDTO $dto): User
    {
        if ($user->role?->name !== 'patient') {
            throw new UnprocessableEntityApiException(
                message: 'Only patient accounts can be modified from this action.',
                errorCode: 'USER_NOT_PATIENT',
            );
        }

        $attributes = [
            'name' => $dto->name,
        ];

        if ($dto->password !== null) {
            $attributes['password'] = $dto->password;
        }

        $user->fill($attributes)->save();

        return $user->load('role');
    }
}
