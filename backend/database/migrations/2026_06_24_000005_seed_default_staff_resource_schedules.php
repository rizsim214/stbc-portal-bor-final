<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_START = '09:00:00';
    private const DEFAULT_END = '16:30:00';
    private const WORKING_DAYS = [1, 2, 3, 4, 5];

    public function up(): void
    {
        $resourceIds = DB::table('resources')
            ->whereNotNull('user_id')
            ->pluck('id');

        $now = now();

        foreach ($resourceIds as $resourceId) {
            $hasSchedules = DB::table('resource_schedules')
                ->where('resource_id', $resourceId)
                ->exists();

            if ($hasSchedules) {
                continue;
            }

            $rows = array_map(
                static fn(int $dayOfWeek): array => [
                    'resource_id' => $resourceId,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => self::DEFAULT_START,
                    'end_time' => self::DEFAULT_END,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                self::WORKING_DAYS,
            );

            DB::table('resource_schedules')->insert($rows);
        }
    }

    public function down(): void
    {
        DB::table('resource_schedules')
            ->whereIn('resource_id', function ($query) {
                $query->select('id')
                    ->from('resources')
                    ->whereNotNull('user_id');
            })
            ->whereIn('day_of_week', self::WORKING_DAYS)
            ->where('start_time', self::DEFAULT_START)
            ->where('end_time', self::DEFAULT_END)
            ->delete();
    }
};
