<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointment_slot_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamps();

            $table->unique('appointment_id');
            $table->index(['start_time', 'end_time']);
        });

        DB::table('appointment_slot_locks')->insertUsing(
            ['appointment_id', 'start_time', 'end_time', 'created_at', 'updated_at'],
            DB::table('appointments')
                ->leftJoin('appointment_resources', 'appointment_resources.appointment_id', '=', 'appointments.id')
                ->whereNull('appointment_resources.appointment_id')
                ->where('appointments.status', '!=', 'cancelled')
                ->selectRaw('appointments.id, appointments.start_time, appointments.end_time, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP')
        );

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "ALTER TABLE appointment_slot_locks ADD CONSTRAINT appointment_slot_locks_no_overlap EXCLUDE USING gist (tsrange(start_time, end_time, '[)') WITH &&)"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE appointment_slot_locks DROP CONSTRAINT IF EXISTS appointment_slot_locks_no_overlap');
        }

        Schema::dropIfExists('appointment_slot_locks');
    }
};
