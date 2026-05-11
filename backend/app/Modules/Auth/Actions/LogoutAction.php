<?php

namespace App\Modules\Auth\Actions;

use Illuminate\Contracts\Auth\Authenticatable;

class LogoutAction
{
    public function execute(Authenticatable $user): void
    {
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }
    }
}
