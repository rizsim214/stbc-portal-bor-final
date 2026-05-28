<?php

namespace App\Modules\Auth\Actions;

use App\Models\Role;
use App\Models\User;
use App\Modules\Auth\DTOs\RegisterDTO;
use Illuminate\Support\Facades\Cache;

class RegisterAction
{
    public function execute(RegisterDTO $dto): array
    {
        $patientRoleId = Cache::rememberForever('roles.patient.id', function (): int {
            return (int) Role::query()->firstOrCreate([
                'name' => 'patient',
            ])->id;
        });

        $user = User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'role_id' => $patientRoleId,
        ]);

        return [
            'token' => $user->createToken($dto->deviceName ?? 'web')->plainTextToken,
            'user' => $user->load('role'),
        ];
    }
}
