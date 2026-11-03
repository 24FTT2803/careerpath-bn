<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The earlier fix, for MariaDB too.
     *
     * Telling every emoji apart was only done where the database
     * reports itself as MySQL. MariaDB, which many hosts run,
     * reports itself as "mariadb", so the fix never reached it:
     * there, most emoji still compared as one value, and a post
     * could only show the handful of emoji the database could tell
     * apart. Safe to run again where the earlier fix already
     * applied.
     */
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE innovation_lab_reactions MODIFY emoji VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
    }

    public function down(): void
    {
        // Left as it is: comparing emoji exactly is never wrong.
    }
};
