<?php

namespace App\Modules\LabResults\Actions;

use App\Models\LabResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ListLabResultsAction
{
    public function execute(User $user): Collection
    {
        $query = LabResult::query()->with('appointment.user');
        $role = $user->role?->name;

        if ($role === 'patient') {
            $query
                ->whereNotNull('released_at')
                ->whereHas('appointment', function ($appointmentQuery) use ($user) {
                    $appointmentQuery->where('user_id', $user->id);
                });
        }

        return $query->orderByDesc('created_at')->get();
    }
}

