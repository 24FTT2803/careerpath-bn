<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tell every emoji apart.
     *
     * MySQL's usual text comparison weighs most emoji the same,
     * so to the database 🔥 and 👍 were one value. A student's
     * second emoji on a post then matched their first: it was
     * found as "already there" and taken back instead of added,
     * and the unique index refused it outright. Compared byte for
     * byte, every emoji is its own, so a student can add as many
     * as they like. The column is also long enough for the
     * longest emoji: subdivision flags and families with skin
     * tones.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE innovation_lab_reactions MODIFY emoji VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE innovation_lab_reactions MODIFY emoji VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
    }
};
