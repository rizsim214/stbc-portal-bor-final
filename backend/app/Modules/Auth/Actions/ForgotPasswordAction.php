<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\ForgotPasswordDTO;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordAction
{
    public function execute(ForgotPasswordDTO $dto): string
    {
        $status = Password::sendResetLink([
            'email' => $dto->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return __($status);
    }
}

