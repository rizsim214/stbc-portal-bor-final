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
        Schema::create('resource_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamps();

            $table->unique(['appointment_id', 'resource_id']);
            $table->index(['resource_id', 'start_time', 'end_time']);
        });

        DB::table('resource_bookings')->insertUsing(
            ['resource_id', 'appointment_id', 'start_time', 'end_time', 'created_at', 'updated_at'],
            DB::table('appointment_resources')
                ->join('appointments', 'appointments.id', '=', 'appointment_resources.appointment_id')
                ->selectRaw('appointment_resources.resource_id, appointment_resources.appointment_id, appointments.start_time, appointments.end_time, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP')
        );

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement(
                "ALTER TABLE resource_bookings ADD CONSTRAINT resource_bookings_no_overlap EXCLUDE USING gist (resource_id WITH =, tstzrange(start_time, end_time, '[)') WITH &&)"
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE resource_bookings DROP CONSTRAINT IF EXISTS resource_bookings_no_overlap');
        }

        Schema::dropIfExists('resource_bookings');
    }
};
