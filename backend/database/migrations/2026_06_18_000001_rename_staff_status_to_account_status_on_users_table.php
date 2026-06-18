<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default('active')->after('role_id');
            $table->index('account_status');
        });

        DB::table('users')
            ->whereNotNull('staff_status')
            ->update(['account_status' => DB::raw('staff_status')]);

        DB::table('users')
            ->whereNull('account_status')
            ->update(['account_status' => 'active']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['staff_status']);
            $table->dropColumn('staff_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('staff_status')->nullable()->after('role_id');
            $table->index('staff_status');
        });

        DB::table('users')
            ->whereNotNull('account_status')
            ->update(['staff_status' => DB::raw('account_status')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['account_status']);
            $table->dropColumn('account_status');
        });
    }
};
