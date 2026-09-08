<?php

namespace Database\Seeders;

use App\Models\FeatureDefinition;
use Illuminate\Database\Seeder;

class FeatureDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            [
                'key' => 'career_recommendations.enabled',
                'name' => 'Career Recommendations',
                'category' => 'Career Recommendations',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 10,
            ],
            [
                'key' => 'career_recommendations.result_count',
                'name' => 'Number of Recommendations',
                'category' => 'Career Recommendations',
                'value_type' => 'number',
                'parent_key' => 'career_recommendations.enabled',
                'sort_order' => 20,
            ],
            [
                'key' => 'career_recommendations.generation_quota',
                'name' => 'Recommendation Generation Quota',
                'category' => 'Career Recommendations',
                'value_type' => 'quota',
                'parent_key' => 'career_recommendations.enabled',
                'sort_order' => 30,
            ],
            [
                'key' => 'career_recommendations.download.enabled',
                'name' => 'Download Recommendations',
                'category' => 'Career Recommendations',
                'value_type' => 'boolean',
                'parent_key' => 'career_recommendations.enabled',
                'sort_order' => 40,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.enabled',
                'name' => 'Detailed Career Analysis',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' => 'career_recommendations.enabled',
                'sort_order' => 100,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.match_score.enabled',
                'name' => 'Match / Readiness Score',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 110,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.matched_competencies.enabled',
                'name' => 'Matched Competencies / Skills',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 120,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.competency_gaps.enabled',
                'name' => 'Competency / Skill Gaps',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 130,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.entry_requirements.enabled',
                'name' => 'Entry Requirements',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 140,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.certification_suggestions.enabled',
                'name' => 'Certification Suggestions',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 150,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.training_suggestions.enabled',
                'name' => 'Training Suggestions',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 160,
            ],
            [
                'key' => 'career_recommendations.detailed_analysis.development_roadmap.enabled',
                'name' => 'Development Roadmap',
                'category' => 'Detailed Career Analysis',
                'value_type' => 'boolean',
                'parent_key' =>
                    'career_recommendations.detailed_analysis.enabled',
                'sort_order' => 170,
            ],
            [
                'key' => 'career_recommendations.comparison.enabled',
                'name' => 'Career Comparison',
                'category' => 'Career Recommendations',
                'value_type' => 'boolean',
                'parent_key' => 'career_recommendations.enabled',
                'sort_order' => 50,
            ],
            [
                'key' => 'career_adviser.enabled',
                'name' => 'Career Adviser',
                'category' => 'Career Adviser',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 200,
            ],
            [
                'key' => 'career_adviser.usage_quota',
                'name' => 'Career Adviser Usage Quota',
                'category' => 'Career Adviser',
                'value_type' => 'quota',
                'parent_key' => 'career_adviser.enabled',
                'sort_order' => 210,
            ],
            [
                'key' => 'recommendation_history.enabled',
                'name' => 'Recommendation History',
                'category' => 'History',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 300,
            ],
            [
                'key' => 'career_adviser.history.enabled',
                'name' => 'Career Adviser History',
                'category' => 'History',
                'value_type' => 'boolean',
                'parent_key' => 'career_adviser.enabled',
                'sort_order' => 310,
            ],
            [
                'key' => 'planning.milestones.enabled',
                'name' => 'Milestones',
                'category' => 'Planning',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 400,
            ],
            [
                'key' => 'planning.development_plan.enabled',
                'name' => 'Development Plan',
                'category' => 'Planning',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 410,
            ],
            [
                'key' => 'analytics.advanced.enabled',
                'name' => 'Advanced Analytics',
                'category' => 'Analytics',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 500,
            ],
            [
                'key' => 'ads.available',
                'name' => 'Advertisements Available',
                'category' => 'Advertising',
                'value_type' => 'boolean',
                'parent_key' => null,
                'sort_order' => 600,
            ],
        ];

        foreach ($features as $feature) {
            $definition = FeatureDefinition::firstOrNew([
                'key' => $feature['key'],
            ]);

            $isNew = ! $definition->exists;

            $definition->fill([
                'name' =>
                    $feature['name'],

                'category' =>
                    $feature['category'],

                'value_type' =>
                    $feature['value_type'],

                'parent_key' =>
                    $feature['parent_key'],

                'sort_order' =>
                    $feature['sort_order'],
            ]);

            if ($isNew) {
                $definition->global_enabled = true;
            }

            $definition->save();
        }
    }
}