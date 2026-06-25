<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete()
                ->unique();
        });

        $staffUsers = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->leftJoin('resources', 'resources.user_id', '=', 'users.id')
            ->where('roles.name', 'staff')
            ->whereNull('resources.user_id')
            ->select([
                'users.id',
                'users.name',
                'users.sub_role',
                'users.account_status',
            ])
            ->get();

        $now = now();

        foreach ($staffUsers as $staffUser) {
            DB::table('resources')->insert([
                'user_id' => $staffUser->id,
                'name' => $staffUser->name,
                'type' => $staffUser->sub_role ?: 'staff',
                'is_active' => strtolower(trim((string) $staffUser->account_status)) !== 'inactive',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
