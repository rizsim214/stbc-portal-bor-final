<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicHoursSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $resourceIds = DB::table('resources')->pluck('id');

        if ($resourceIds->isEmpty()) {
            return;
        }

        $rows = [];
        $now = now();

        foreach ($resourceIds as $resourceId) {
            for ($dayOfWeek = 1; $dayOfWeek <= 6; $dayOfWeek++) {
                $rows[] = [
                    'resource_id' => $resourceId,
                    'day_of_week' => $dayOfWeek,
                    'start_time' => '08:30:00',
                    'end_time' => '17:30:00',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('resource_schedules')->upsert(
            $rows,
            ['resource_id', 'day_of_week', 'start_time'],
            ['end_time', 'updated_at']
        );
    }
}

