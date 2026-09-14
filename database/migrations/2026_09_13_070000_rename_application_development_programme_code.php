<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Correct the Application Development programme code.
     *
     * It was seeded as APP-DEV, but the institution calls it
     * DADT, which is what the classes beneath it are named after.
     * Renamed rather than recreated so every membership,
     * sponsorship and advertisement pointing at it survives.
     */
    public function up(): void
    {
        DB::table('organisation_groups')
            ->where('code', 'APP-DEV')
            ->update(['code' => 'DADT']);
    }

    public function down(): void
    {
        DB::table('organisation_groups')
            ->where('code', 'DADT')
            ->update(['code' => 'APP-DEV']);
    }
};
