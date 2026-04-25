<?php

namespace App\Modules\Auth\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Auth\DTOs\RegisterDTO;

class RegisterAction
{
    public function execute(RegisterDTO $dto): array
    {
        $patientRole = Role::query()->firstOrCreate([
            'name' => 'patient',
        ]);

        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'role_id' => $patientRole->id,
        ]);

        return [
            'token' => $user->createToken($dto->deviceName ?? 'web')->plainTextToken,
            'user' => $user->load('role'),
        ];
    }
}
