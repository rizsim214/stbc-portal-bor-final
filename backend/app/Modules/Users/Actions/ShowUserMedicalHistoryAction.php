<?php

namespace App\Modules\Users\Actions;

use App\Models\User;
use Illuminate\Support\Collection;

class ShowUserMedicalHistoryAction
{
    public function execute(User $targetUser): array
    {
        $targetUser->load('role');

        $labResults = $targetUser->appointments()
            ->with(['labResult', 'type'])
            ->orderByDesc('start_time')
            ->get()
            ->map(function ($appointment) {
                $labResult = $appointment->labResult;

                if (!$labResult) {
                    return [
                        'id' => 'appointment-' . $appointment->id,
                        'kind' => 'appointment',
                        'date' => optional($appointment->start_time)->toDateTimeString() ?? (string) $appointment->start_time,
                        'title' => $appointment->type?->name ?? 'Appointment',
                        'summary' => $appointment->notes ?? 'Appointment completed.',
                        'released_at' => null,
                        'result_data' => null,
                        'appointment' => [
                            'id' => $appointment->id,
                            'notes' => $appointment->notes,
                            'start_time' => $appointment->start_time,
                            'end_time' => $appointment->end_time,
                        ],
                    ];
                }

                return [
                    'id' => $labResult->id,
                    'kind' => 'lab_result',
                    'date' => optional($appointment->start_time)->toDateTimeString() ?? (string) $appointment->start_time,
                    'title' => $appointment->type?->name ?? 'Lab Result',
                    'summary' => is_array($labResult->result_data) && isset($labResult->result_data['summary'])
                        ? $labResult->result_data['summary']
                        : ($appointment->notes ?? 'Lab result recorded.'),
                    'released_at' => $labResult->released_at,
                    'result_data' => $labResult->result_data,
                    'appointment' => [
                        'id' => $appointment->id,
                        'notes' => $appointment->notes,
                        'start_time' => $appointment->start_time,
                        'end_time' => $appointment->end_time,
                    ],
                    'file_path' => $labResult->file_path,
                ];
            });

        return [
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role' => $targetUser->role,
                'created_at' => $targetUser->created_at,
                'updated_at' => $targetUser->updated_at,
            ],
            'medical_history' => $labResults,
        ];
    }
}
