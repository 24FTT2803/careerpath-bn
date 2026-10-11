<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When a note first went live for students.
     *
     * A note can be drafted and published later, so its creation
     * date is not when students first saw it. This drives the
     * "new" badge and makes sure students are told only once.
     */
    public function up(): void
    {
        Schema::table('innovation_lab_notes', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('is_published');
        });

        /* Notes already live count as published when they were made. */
        DB::table('innovation_lab_notes')
            ->where('is_published', true)
            ->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('innovation_lab_notes', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
