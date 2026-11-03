<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An earlier draft of the previous migration added event_starts_on
     * and event_ends_on instead of show_until, and ran on a local
     * database before it was replaced. This brings such a database in
     * line; anywhere the current version ran, it does nothing.
     */
    public function up(): void
    {
        if (Schema::hasColumn('innovation_lab_notes', 'event_starts_on')) {
            Schema::table('innovation_lab_notes', function (Blueprint $table) {
                $table->dropColumn(['event_starts_on', 'event_ends_on']);
            });
        }

        if (! Schema::hasColumn('innovation_lab_notes', 'show_until')) {
            Schema::table('innovation_lab_notes', function (Blueprint $table) {
                $table->date('show_until')->nullable()->after('body');
            });
        }
    }

    /**
     * Nothing to undo: the columns it may have dropped were never used.
     */
    public function down(): void {}
};
