<?php

namespace App\Modules\Scheduling\Services;

use App\Models\Appointment;
use App\Models\ResourceSchedule;
use App\Models\ScheduleException;
use App\Modules\Scheduling\DTOs\SchedulingAvailabilityDTO;
use Carbon\CarbonImmutable;

class SchedulingService
{
    private const DEFAULT_SLOT_MINUTES = 30;

    /**
     * @param  array<int>  $resourceIds
     */
    public function isAvailable(array $resourceIds, string $start, string $end): bool
    {
        return $this->isWithinResourceSchedules($resourceIds, $start, $end)
            && $this->isConflictFree($resourceIds, $start, $end);
    }

    /**
     * @return array<string, mixed>
     */
    public function getAvailableSlots(SchedulingAvailabilityDTO $dto): array
    {
        $date = CarbonImmutable::parse($dto->date)->startOfDay();
        $intervals = $this->getCommonIntervalsForDate($dto->resourceIds, $date);
        $slots = [];

        foreach ($intervals as $interval) {
            $cursor = $date->setTime(
                (int) floor($interval['start'] / 60),
                $interval['start'] % 60
            );
            $windowEnd = $date->setTime(
                (int) floor($interval['end'] / 60),
                $interval['end'] % 60
            );

            while ($cursor->addMinutes(self::DEFAULT_SLOT_MINUTES) <= $windowEnd) {
                $slotEnd = $cursor->addMinutes(self::DEFAULT_SLOT_MINUTES);

                if ($this->isConflictFree($dto->resourceIds, $cursor->toDateTimeString(), $slotEnd->toDateTimeString())) {
                    $slots[] = [
                        'start_time' => $cursor->toDateTimeString(),
                        'end_time' => $slotEnd->toDateTimeString(),
                    ];
                }

                $cursor = $slotEnd;
            }
        }

        return [
            'date' => $date->toDateString(),
            'appointment_type_id' => $dto->appointmentTypeId,
            'resource_ids' => $dto->resourceIds,
            'slot_minutes' => self::DEFAULT_SLOT_MINUTES,
            'slots' => $slots,
        ];
    }

    /**
     * @param  array<int>  $resourceIds
     */
    private function isWithinResourceSchedules(array $resourceIds, string $start, string $end): bool
    {
        $startAt = CarbonImmutable::parse($start);
        $endAt = CarbonImmutable::parse($end);

        if ($startAt->toDateString() !== $endAt->toDateString()) {
            return false;
        }

        $intervals = $this->getCommonIntervalsForDate($resourceIds, $startAt->startOfDay());

        if ($intervals === []) {
            return false;
        }

        $startMinutes = $startAt->hour * 60 + $startAt->minute;
        $endMinutes = $endAt->hour * 60 + $endAt->minute;

        foreach ($intervals as $interval) {
            if ($startMinutes >= $interval['start'] && $endMinutes <= $interval['end']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int>  $resourceIds
     */
    private function isConflictFree(array $resourceIds, string $start, string $end): bool
    {
        return !Appointment::query()
            ->whereHas('resources', function ($q) use ($resourceIds) {
                $q->whereIn('resources.id', $resourceIds);
            })
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereBetween('start_time', [$start, $end])
                    ->orWhereBetween('end_time', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_time', '<=', $start)
                            ->where('end_time', '>=', $end);
                    });
            })
            ->exists();
    }

    /**
     * @param  array<int>  $resourceIds
     * @return array<int, array{start:int,end:int}>
     */
    private function getCommonIntervalsForDate(array $resourceIds, CarbonImmutable $date): array
    {
        $dayOfWeek = $date->dayOfWeek;
        $schedules = ResourceSchedule::query()
            ->whereIn('resource_id', $resourceIds)
            ->where('day_of_week', $dayOfWeek)
            ->get(['resource_id', 'start_time', 'end_time'])
            ->groupBy('resource_id');

        $commonIntervals = [];
        $initialized = false;

        foreach ($resourceIds as $resourceId) {
            $resourceSchedules = $schedules->get($resourceId);

            if (!$resourceSchedules || $resourceSchedules->isEmpty()) {
                return [];
            }

            $intervals = $resourceSchedules
                ->map(function ($schedule): array {
                    $start = CarbonImmutable::parse($schedule->start_time);
                    $end = CarbonImmutable::parse($schedule->end_time);

                    return [
                        'start' => $start->hour * 60 + $start->minute,
                        'end' => $end->hour * 60 + $end->minute,
                    ];
                })
                ->values()
                ->all();

            if (!$initialized) {
                $commonIntervals = $intervals;
                $initialized = true;
                continue;
            }

            $commonIntervals = $this->intersectIntervals($commonIntervals, $intervals);

            if ($commonIntervals === []) {
                return [];
            }
        }

        return $this->applyScheduleExceptions($resourceIds, $date, $commonIntervals);
    }

    /**
     * @param  array<int>  $resourceIds
     * @param  array<int, array{start:int,end:int}>  $intervals
     * @return array<int, array{start:int,end:int}>
     */
    private function applyScheduleExceptions(array $resourceIds, CarbonImmutable $date, array $intervals): array
    {
        if ($intervals === []) {
            return [];
        }

        $exceptions = ScheduleException::query()
            ->whereDate('exception_date', $date->toDateString())
            ->where(function ($query) use ($resourceIds) {
                $query->whereNull('resource_id')
                    ->orWhereIn('resource_id', $resourceIds);
            })
            ->get(['resource_id', 'start_time', 'end_time']);

        if ($exceptions->isEmpty()) {
            return $intervals;
        }

        $fullDayGlobalExceptionExists = $exceptions
            ->whereNull('resource_id')
            ->contains(fn ($exception): bool => $exception->start_time === null && $exception->end_time === null);

        if ($fullDayGlobalExceptionExists) {
            return [];
        }

        $resourceWithFullDayException = $exceptions
            ->whereNotNull('resource_id')
            ->whereIn('resource_id', $resourceIds)
            ->contains(fn ($exception): bool => $exception->start_time === null && $exception->end_time === null);

        if ($resourceWithFullDayException) {
            return [];
        }

        $blockedIntervals = $exceptions
            ->filter(fn ($exception): bool => $exception->start_time !== null && $exception->end_time !== null)
            ->map(function ($exception): array {
                $start = CarbonImmutable::parse($exception->start_time);
                $end = CarbonImmutable::parse($exception->end_time);

                return [
                    'start' => $start->hour * 60 + $start->minute,
                    'end' => $end->hour * 60 + $end->minute,
                ];
            })
            ->values()
            ->all();

        if ($blockedIntervals === []) {
            return $intervals;
        }

        return $this->subtractIntervals($intervals, $this->mergeIntervals($blockedIntervals));
    }

    /**
     * @param  array<int, array{start:int,end:int}>  $left
     * @param  array<int, array{start:int,end:int}>  $right
     * @return array<int, array{start:int,end:int}>
     */
    private function intersectIntervals(array $left, array $right): array
    {
        $result = [];

        foreach ($left as $leftInterval) {
            foreach ($right as $rightInterval) {
                $start = max($leftInterval['start'], $rightInterval['start']);
                $end = min($leftInterval['end'], $rightInterval['end']);

                if ($start < $end) {
                    $result[] = [
                        'start' => $start,
                        'end' => $end,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param  array<int, array{start:int,end:int}>  $baseIntervals
     * @param  array<int, array{start:int,end:int}>  $blockedIntervals
     * @return array<int, array{start:int,end:int}>
     */
    private function subtractIntervals(array $baseIntervals, array $blockedIntervals): array
    {
        $result = [];

        foreach ($baseIntervals as $base) {
            $fragments = [$base];

            foreach ($blockedIntervals as $block) {
                $nextFragments = [];

                foreach ($fragments as $fragment) {
                    if ($block['end'] <= $fragment['start'] || $block['start'] >= $fragment['end']) {
                        $nextFragments[] = $fragment;
                        continue;
                    }

                    if ($block['start'] > $fragment['start']) {
                        $nextFragments[] = [
                            'start' => $fragment['start'],
                            'end' => $block['start'],
                        ];
                    }

                    if ($block['end'] < $fragment['end']) {
                        $nextFragments[] = [
                            'start' => $block['end'],
                            'end' => $fragment['end'],
                        ];
                    }
                }

                $fragments = $nextFragments;

                if ($fragments === []) {
                    break;
                }
            }

            foreach ($fragments as $fragment) {
                if ($fragment['start'] < $fragment['end']) {
                    $result[] = $fragment;
                }
            }
        }

        return $result;
    }

    /**
     * @param  array<int, array{start:int,end:int}>  $intervals
     * @return array<int, array{start:int,end:int}>
     */
    private function mergeIntervals(array $intervals): array
    {
        if ($intervals === []) {
            return [];
        }

        usort($intervals, static function (array $a, array $b): int {
            return $a['start'] <=> $b['start'];
        });

        $merged = [$intervals[0]];

        foreach (array_slice($intervals, 1) as $interval) {
            $lastIndex = count($merged) - 1;
            $last = $merged[$lastIndex];

            if ($interval['start'] <= $last['end']) {
                $merged[$lastIndex]['end'] = max($last['end'], $interval['end']);
                continue;
            }

            $merged[] = $interval;
        }

        return $merged;
    }
}
