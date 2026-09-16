<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make Premium ad free.
     *
     * The seeder uses firstOrCreate, so it will not change a
     * value that already exists. Databases seeded before this
     * change need the row updated directly.
     */
    public function up(): void
    {
        $premiumId = DB::table('plans')
            ->where('code', 'premium')
            ->value('id');

        if ($premiumId === null) {
            return;
        }

        DB::table('plan_features')
            ->where('plan_id', $premiumId)
            ->where('key', 'ads.available')
            ->update(['value' => json_encode(false)]);
    }

    public function down(): void
    {
        $premiumId = DB::table('plans')
            ->where('code', 'premium')
            ->value('id');

        if ($premiumId === null) {
            return;
        }

        DB::table('plan_features')
            ->where('plan_id', $premiumId)
            ->where('key', 'ads.available')
            ->update(['value' => json_encode(true)]);
    }
};
