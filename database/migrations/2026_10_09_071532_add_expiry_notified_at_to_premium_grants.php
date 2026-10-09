<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remember which Premium grants have already told their
     * students "your Premium has ended", so nobody is told twice.
     */
    public function up(): void
    {
        foreach (['user_plan_grants', 'sponsored_access_grants'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->timestamp('expiry_notified_at')->nullable();
            });

            /*
             * Grants that ended before this update are treated as
             * already announced, so old trials do not suddenly send
             * a pile of "ended" messages.
             */
            DB::table($table)
                ->whereNotNull('ends_at')
                ->where('ends_at', '<=', now())
                ->update(['expiry_notified_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (['user_plan_grants', 'sponsored_access_grants'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('expiry_notified_at');
            });
        }
    }
};
