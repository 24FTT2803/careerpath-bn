<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The last day a note is shown to students. Null means it never
     * expires; past that day only admins see it.
     */
    public function up(): void
    {
        Schema::table('innovation_lab_notes', function (Blueprint $table) {
            $table->date('show_until')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('innovation_lab_notes', function (Blueprint $table) {
            $table->dropColumn('show_until');
        });
    }
};
