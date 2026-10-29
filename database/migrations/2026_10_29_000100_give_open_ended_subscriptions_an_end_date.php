<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Give every running student subscription an end date.
 *
 * A subscription now lasts one month. Before this, upgrading
 * created a grant with no end date, so those students would stay
 * on Premium for ever. They are given a month from today rather
 * than from when they upgraded: counting from the upgrade would
 * end most of them at once, unannounced by anything they did.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('user_plan_grants')
            ->where('source', 'self')
            ->where('is_active', true)
            ->whereNull('ends_at')
            ->update([
                'ends_at' => now()->addMonth(),
                'updated_at' => now(),
            ]);
    }

    /**
     * An end date cannot be told apart from one set by an
     * upgrade after this ran, so nothing is undone.
     */
    public function down(): void {}
};
