<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $free = Plan::firstOrCreate(
            [
                'code' => 'free',
            ],
            [
                'name' => 'Free',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $premium = Plan::firstOrCreate(
            [
                'code' => 'premium',
            ],
            [
                'name' => 'Premium',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        /*
         * Remove the original simple integer limit.
         * It has been replaced by the configurable
         * quota structure below.
         */
        PlanFeature::query()
            ->whereIn(
                'plan_id',
                [
                    $free->id,
                    $premium->id,
                ]
            )
            ->where(
                'key',
                'career_recommendations.generation_limit'
            )
            ->delete();

        $this->seedFeatures(
            $free,
            [
                'career_recommendations.enabled'
                    => true,

                'career_recommendations.result_count'
                    => 3,

                /*
                 * Initial seed only.
                 *
                 * Admin can later change BOTH the amount
                 * and interval to any valid values such as
                 * 100 every 7 days or 5 every 3 months.
                 */
                'career_recommendations.generation_quota'
                    => [
                        'mode' => 'recurring',
                        'amount' => 3,
                        'period_value' => 1,
                        'period_unit' => 'month',
                    ],

                'career_recommendations.download.enabled'
                    => false,

                'career_recommendations.detailed_analysis.enabled'
                    => true,

                'career_recommendations.detailed_analysis.match_score.enabled'
                    => true,

                'career_recommendations.detailed_analysis.matched_competencies.enabled'
                    => true,

                'career_recommendations.detailed_analysis.competency_gaps.enabled'
                    => true,

                'career_recommendations.detailed_analysis.entry_requirements.enabled'
                    => true,

                'career_recommendations.detailed_analysis.certification_suggestions.enabled'
                    => true,

                'career_recommendations.detailed_analysis.training_suggestions.enabled'
                    => true,

                'career_recommendations.detailed_analysis.development_roadmap.enabled'
                    => true,

                'career_recommendations.comparison.enabled'
                    => false,

                'career_adviser.enabled'
                    => true,

                /*
                 * No adviser usage restriction is being
                 * introduced yet.
                 */
                'career_adviser.usage_quota'
                    => [
                        'mode' => 'unlimited',
                    ],

                'recommendation_history.enabled'
                    => false,

                'career_adviser.history.enabled'
                    => false,

                'planning.milestones.enabled'
                    => true,

                'planning.development_plan.enabled'
                    => true,

                'analytics.advanced.enabled'
                    => false,

                'ads.available'
                    => true,
            ]
        );

        $this->seedFeatures(
            $premium,
            [
                'career_recommendations.enabled'
                    => true,

                'career_recommendations.result_count'
                    => 5,

                'career_recommendations.generation_quota'
                    => [
                        'mode' => 'unlimited',
                    ],

                'career_recommendations.download.enabled'
                    => true,

                'career_recommendations.detailed_analysis.enabled'
                    => true,

                'career_recommendations.detailed_analysis.match_score.enabled'
                    => true,

                'career_recommendations.detailed_analysis.matched_competencies.enabled'
                    => true,

                'career_recommendations.detailed_analysis.competency_gaps.enabled'
                    => true,

                'career_recommendations.detailed_analysis.entry_requirements.enabled'
                    => true,

                'career_recommendations.detailed_analysis.certification_suggestions.enabled'
                    => true,

                'career_recommendations.detailed_analysis.training_suggestions.enabled'
                    => true,

                'career_recommendations.detailed_analysis.development_roadmap.enabled'
                    => true,

                'career_recommendations.comparison.enabled'
                    => true,

                'career_adviser.enabled'
                    => true,

                'career_adviser.usage_quota'
                    => [
                        'mode' => 'unlimited',
                    ],

                'recommendation_history.enabled'
                    => true,

                'career_adviser.history.enabled'
                    => true,

                'planning.milestones.enabled'
                    => true,

                'planning.development_plan.enabled'
                    => true,

                'analytics.advanced.enabled'
                    => true,

                'ads.available'
                    => true,
            ]
        );
    }

    private function seedFeatures(
        Plan $plan,
        array $features
    ): void {
        foreach ($features as $key => $value) {
            PlanFeature::firstOrCreate(
                [
                    'plan_id' =>
                        $plan->id,

                    'key' =>
                        $key,
                ],
                [
                    'value' =>
                        $value,
                ]
            );
        }
    }
}