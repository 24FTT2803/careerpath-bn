<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_generations', function (Blueprint $table) {
            /*
             * Nullable on purpose.
             *
             * Generations backfilled from rows written before
             * Task 6 have no recorded profile, and inventing one
             * from today's data would assert something untrue.
             * A generation without a snapshot is simply never
             * evaluated for outdatedness.
             */
            $table
                ->foreignId('profile_snapshot_id')
                ->nullable()
                ->after('user_id')
                ->constrained('profile_snapshots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_generations', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'profile_snapshot_id'
            );
        });
    }
};
