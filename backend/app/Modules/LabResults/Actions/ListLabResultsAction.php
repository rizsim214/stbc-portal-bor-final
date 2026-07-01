<?php

namespace App\Modules\LabResults\Actions;

use App\Models\LabResult;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListLabResultsAction
{
    public function execute(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $query = LabResult::query()->with([
            'appointment.user',
            'appointment.type',
        ]);

        if ($user->hasAnyRole(['user', 'patient'])) {
            $query
                ->whereNotNull('released_at')
                ->whereHas('appointment', function ($appointmentQuery) use ($user) {
                    $appointmentQuery->where('user_id', $user->id);
                });
        }

        return $query
            ->orderByDesc('released_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
