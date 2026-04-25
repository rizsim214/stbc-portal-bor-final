<?php

namespace App\Modules\Auth\Actions;

use App\Models\User;
use App\Modules\Auth\DTOs\RegisterDTO;

class RegisterAction
{
    public function execute(RegisterDTO $dto): array
    {
        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);

        return [
            'token' => $user->createToken($dto->deviceName ?? 'web')->plainTextToken,
            'user' => $user,
        ];
    }
}

