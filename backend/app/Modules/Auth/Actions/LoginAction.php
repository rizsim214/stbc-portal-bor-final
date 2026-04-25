<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function execute(LoginDTO $dto): array
    {
        $user = User::query()->where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        return [
            'token' => $user->createToken($dto->deviceName ?? 'web')->plainTextToken,
            'user' => $user->load('role'),
        ];
    }
}
