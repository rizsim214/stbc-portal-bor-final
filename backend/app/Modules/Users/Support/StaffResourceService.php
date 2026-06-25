<?php

namespace App\Modules\Users\Support;

use App\Models\Resource;
use App\Models\ResourceSchedule;
use App\Models\User;
use App\Modules\Shared\Exceptions\UnprocessableEntityApiException;
use Illuminate\Support\Collection;

class StaffResourceService
{
    private const DEFAULT_START = '09:00:00';
    private const DEFAULT_END = '16:30:00';
    private const WORKING_DAYS = [1, 2, 3, 4, 5];

    public function syncForStaffUser(User $user): Resource
    {
        $resource = Resource::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'type' => $user->sub_role ?: 'staff',
                'is_active' => strtolower(trim((string) $user->account_status)) !== 'inactive',
                'is_available' => true,
            ],
        );

        $this->ensureDefaultSchedules($resource);

        return $resource;
    }

    public function ensureDefaultSchedules(Resource $resource): void
    {
        $hasSchedules = ResourceSchedule::query()
            ->where('resource_id', $resource->id)
            ->exists();

        if ($hasSchedules) {
            return;
        }

        $rows = array_map(
            fn(int $dayOfWeek): array => [
                'resource_id' => $resource->id,
                'day_of_week' => $dayOfWeek,
                'start_time' => self::DEFAULT_START,
                'end_time' => self::DEFAULT_END,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            self::WORKING_DAYS,
        );

        ResourceSchedule::query()->insert($rows);
    }

    /**
     * @return array<int, array{day_of_week:int,is_enabled:bool,start_time:?string,end_time:?string}>
     */
    public function getWeeklySchedule(Resource $resource): array
    {
        $scheduleMap = ResourceSchedule::query()
            ->where('resource_id', $resource->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get(['day_of_week', 'start_time', 'end_time'])
            ->groupBy('day_of_week');

        return collect(range(0, 6))
            ->map(function (int $dayOfWeek) use ($scheduleMap): array {
                /** @var Collection<int, ResourceSchedule> $entries */
                $entries = $scheduleMap->get($dayOfWeek, collect());
                $entry = $entries->first();

                return [
                    'day_of_week' => $dayOfWeek,
                    'is_enabled' => $entry !== null,
                    'start_time' => $entry?->start_time,
                    'end_time' => $entry?->end_time,
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array{day_of_week:int,is_enabled:bool,start_time:?string,end_time:?string}>  $days
     */
    public function replaceWeeklySchedule(Resource $resource, array $days): void
    {
        foreach ($days as $day) {
            if (!$day['is_enabled']) {
                continue;
            }

            if ($day['start_time'] === null || $day['end_time'] === null || $day['start_time'] >= $day['end_time']) {
                throw new UnprocessableEntityApiException(
                    message: 'Each enabled day must have a valid start and end time.',
                    errorCode: 'INVALID_STAFF_SCHEDULE',
                );
            }
        }

        ResourceSchedule::query()
            ->where('resource_id', $resource->id)
            ->delete();

        $rows = collect($days)
            ->filter(fn(array $day): bool => $day['is_enabled'])
            ->map(fn(array $day): array => [
                'resource_id' => $resource->id,
                'day_of_week' => $day['day_of_week'],
                'start_time' => $day['start_time'],
                'end_time' => $day['end_time'],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if ($rows !== []) {
            ResourceSchedule::query()->insert($rows);
        }
    }
}
