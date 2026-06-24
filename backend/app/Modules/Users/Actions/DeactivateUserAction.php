<?php

namespace App\Modules\Users\Actions;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($userId, $nextStatus): void {
            User::query()
                ->whereKey($userId)
                ->update(['account_status' => $nextStatus]);

            Resource::query()
                ->where('user_id', $userId)
                ->update([
                    'is_active' => $nextStatus !== 'inactive',
                    'is_available' => $nextStatus !== 'inactive',
                ]);
        });

        return User::query()
            ->with('role')
            ->findOrFail($userId);
    }
}
