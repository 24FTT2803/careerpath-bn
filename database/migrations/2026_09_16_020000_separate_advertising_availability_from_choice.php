<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Separate "advertising exists on this plan" from "the
     * student may switch it off".
     *
     * ads.available had been overloaded to mean advertising is
     * compulsory, which switched off the position slots beneath
     * it as a side effect. A premium student who opted in then
     * saw nothing at all.
     */
    public function up(): void
    {
        $this->defineOptionalFeature();

        $premiumId = DB::table('plans')
            ->where('code', 'premium')
            ->value('id');

        $freeId = DB::table('plans')
            ->where('code', 'free')
            ->value('id');

        /*
         * Advertising is possible on both plans again, so the
         * position slots below it resolve normally.
         */
        if ($premiumId !== null) {
            DB::table('plan_features')
                ->where('plan_id', $premiumId)
                ->where('key', 'ads.available')
                ->update(['value' => json_encode(true)]);

            $this->setOptional($premiumId, true);
        }

        if ($freeId !== null) {
            $this->setOptional($freeId, false);
        }
    }

    private function defineOptionalFeature(): void
    {
        $exists = DB::table('feature_definitions')
            ->where('key', 'ads.optional.enabled')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('feature_definitions')->insert([
            'key' => 'ads.optional.enabled',
            'name' => 'Students May Turn Ads Off',
            'category' => 'Advertising',
            'value_type' => 'boolean',
            'parent_key' => 'ads.available',
            'sort_order' => 607,
            'global_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function setOptional(int $planId, bool $optional): void
    {
        $exists = DB::table('plan_features')
            ->where('plan_id', $planId)
            ->where('key', 'ads.optional.enabled')
            ->exists();

        if ($exists) {
            DB::table('plan_features')
                ->where('plan_id', $planId)
                ->where('key', 'ads.optional.enabled')
                ->update(['value' => json_encode($optional)]);

            return;
        }

        DB::table('plan_features')->insert([
            'plan_id' => $planId,
            'key' => 'ads.optional.enabled',
            'value' => json_encode($optional),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('plan_features')
            ->where('key', 'ads.optional.enabled')
            ->delete();

        DB::table('feature_definitions')
            ->where('key', 'ads.optional.enabled')
            ->delete();
    }
};
