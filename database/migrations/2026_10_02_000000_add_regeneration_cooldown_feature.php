<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the regeneration cooldown to existing installations.
     *
     * The seeders create this for a fresh database, but both use
     * firstOrCreate, so an installation that already holds the
     * catalogue would never see it.
     */
    public function up(): void
    {
        $this->defineFeature();
        $this->seedPlanValues();
    }

    private function defineFeature(): void
    {
        $exists = DB::table('feature_definitions')
            ->where(
                'key',
                'career_recommendations.regeneration_cooldown'
            )
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('feature_definitions')->insert([
            'key' => 'career_recommendations.regeneration_cooldown',
            'name' => 'Regeneration Cooldown (seconds)',
            'category' => 'Career Recommendations',
            'value_type' => 'number',
            'parent_key' => 'career_recommendations.enabled',
            'sort_order' => 603,
            'global_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedPlanValues(): void
    {
        $plans = DB::table('plans')->pluck('id');

        foreach ($plans as $planId) {
            $exists = DB::table('plan_features')
                ->where('plan_id', $planId)
                ->where(
                    'key',
                    'career_recommendations.regeneration_cooldown'
                )
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('plan_features')->insert([
                'plan_id' => $planId,
                'key' => 'career_recommendations.regeneration_cooldown',
                'value' => json_encode(30),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('plan_features')
            ->where(
                'key',
                'career_recommendations.regeneration_cooldown'
            )
            ->delete();

        DB::table('feature_definitions')
            ->where(
                'key',
                'career_recommendations.regeneration_cooldown'
            )
            ->delete();
    }
};
