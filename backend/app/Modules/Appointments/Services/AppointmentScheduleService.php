<?php

namespace App\Modules\Appointments\Services;

use App\Models\Appointment;
use Carbon\CarbonImmutable;

class AppointmentScheduleService
{
    private const SLOT_MINUTES = 30;

    private const CLINIC_START_HOUR = 9;
    private const CLINIC_START_MINUTE = 0;
    private const CLINIC_END_HOUR = 16;
    private const CLINIC_END_MINUTE = 30;

    public function isSlotAvailable(string $start, string $end, ?int $ignoreAppointmentId = null): bool
    {
        return !$this->overlapQuery($start, $end, $ignoreAppointmentId)->exists();
    }

    /**
     * @return array<int, array{start_time:string,end_time:string}>
     */
    public function getAvailableSlots(string $date, ?int $ignoreAppointmentId = null): array
    {
        $day = CarbonImmutable::parse($date)->startOfDay();
        $clinicStart = $day->setTime(self::CLINIC_START_HOUR, self::CLINIC_START_MINUTE);
        $clinicEnd = $day->setTime(self::CLINIC_END_HOUR, self::CLINIC_END_MINUTE);

        $blockedIntervals = $this->overlapQuery(
            $clinicStart->toDateTimeString(),
            $clinicEnd->toDateTimeString(),
            $ignoreAppointmentId,
        )
            ->orderBy('start_time')
            ->get(['start_time', 'end_time'])
            ->map(static function (Appointment $appointment): array {
                return [
                    'start' => CarbonImmutable::parse($appointment->start_time),
                    'end' => CarbonImmutable::parse($appointment->end_time),
                ];
            })
            ->all();

        $slots = [];
        $cursor = $clinicStart;

        foreach ($blockedIntervals as $interval) {
            while ($cursor->addMinutes(self::SLOT_MINUTES) <= $interval['start']) {
                $slotEnd = $cursor->addMinutes(self::SLOT_MINUTES);
                $slots[] = [
                    'start_time' => $cursor->toDateTimeString(),
                    'end_time' => $slotEnd->toDateTimeString(),
                ];
                $cursor = $slotEnd;
            }

            if ($cursor < $interval['end']) {
                $cursor = $interval['end'];
            }
        }

        while ($cursor->addMinutes(self::SLOT_MINUTES) <= $clinicEnd) {
            $slotEnd = $cursor->addMinutes(self::SLOT_MINUTES);
            $slots[] = [
                'start_time' => $cursor->toDateTimeString(),
                'end_time' => $slotEnd->toDateTimeString(),
            ];
            $cursor = $slotEnd;
        }

        return $slots;
    }

    public function isValidSlotWindow(string $start, string $end): bool
    {
        $startAt = CarbonImmutable::parse($start);
        $endAt = CarbonImmutable::parse($end);

        if ($startAt->toDateString() !== $endAt->toDateString()) {
            return false;
        }

        if ((int) $startAt->diffInMinutes($endAt) !== self::SLOT_MINUTES) {
            return false;
        }

        $clinicStart = $startAt->startOfDay()->setTime(self::CLINIC_START_HOUR, self::CLINIC_START_MINUTE);
        $clinicEnd = $startAt->startOfDay()->setTime(self::CLINIC_END_HOUR, self::CLINIC_END_MINUTE);

        return $startAt >= $clinicStart && $endAt <= $clinicEnd;
    }

    private function overlapQuery(string $start, string $end, ?int $ignoreAppointmentId = null)
    {
        $query = Appointment::query()
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($ignoreAppointmentId !== null) {
            $query->where('id', '!=', $ignoreAppointmentId);
        }

        return $query;
    }
}
