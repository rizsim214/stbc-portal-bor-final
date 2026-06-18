<?php

namespace App\Modules\Users\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeactivateUserAction
{
    public function execute(int $userId): User
    {
        $user = User::query()->find($userId);

        if (! $user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$userId]);
        }

        $currentStatus = strtolower(trim((string) ($user->account_status ?? 'active')));
        $nextStatus = $currentStatus === 'inactive' ? 'active' : 'inactive';

        User::query()
            ->whereKey($userId)
            ->update(['account_status' => $nextStatus]);

        return User::query()
            ->with('role')
            ->findOrFail($userId);
    }
}
