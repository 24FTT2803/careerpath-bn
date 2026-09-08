<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $free = Plan::updateOrCreate(
            [
                'code' => 'free',
            ],
            [
                'name' => 'Free',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $premium = Plan::updateOrCreate(
            [
                'code' => 'premium',
            ],
            [
                'name' => 'Premium',
                'is_active' => true,
                'is_default' => false,
            ]
        );

        $this->seedFeatures(
            $free,
            [
                'career_recommendations.enabled'
                    => true,

                /*
                 * This is configuration data rather than
                 * an enforced limit yet. Usage enforcement
                 * can consume this value later.
                 */
                'career_recommendations.generation_limit'
                    => 3,

                'career_adviser.enabled'
                    => true,

                'ads.available'
                    => true,
            ]
        );

        $this->seedFeatures(
            $premium,
            [
                'career_recommendations.enabled'
                    => true,

                /*
                 * Null means no configured generation
                 * limit for this plan.
                 */
                'career_recommendations.generation_limit'
                    => null,

                'career_adviser.enabled'
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
            PlanFeature::updateOrCreate(
                [
                    'plan_id' => $plan->id,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                ]
            );
        }
    }
}