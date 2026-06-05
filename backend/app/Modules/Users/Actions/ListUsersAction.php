<?php

namespace App\Modules\Users\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListUsersAction
{
    public function execute(): Collection
    {
        return User::query()
            ->with('role')
            ->orderBy('name')
            ->get();
    }
}
