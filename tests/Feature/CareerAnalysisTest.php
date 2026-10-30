<?php

use App\Models\BIICFCareer;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\FeatureDefinition;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlanGrant;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed([FeatureDefinitionSeeder::class, PlanSeeder::class]);
    $this->student = User::factory()->create(['role' => 'student']);
    $role = BiicfJobRole::create([
        'title' => 'Analysis Test Engineer',
        'slug' => 'analysis-test-engineer',
        'sub_sector_id' => BiicfSubSector::create([
            'name' => 'Analysis Test Sector',
            'slug' => 'analysis-test-sector',
        ])->id,
    ]);
    $role->descriptionPoints()->create([
        'text' => 'Builds reliable software for local businesses.',
        'sort_order' => 1,
    ]);
    $generation = $this->student->recommendationGenerations()->create([
        'generation_number' => 1,
        'status' => 'current',
        'driver' => 'mock',
        'recommendation_count' => 1,
        'generated_at' => now(),
    ]);
    $this->recommendation = $generation->recommendations()->create([
        'user_id' => $this->student->id,
        'biicf_job_role_id' => $role->id,
        'rank' => 1,
        'match_score' => 83,
        'career_readiness_score' => 64,
        'matched_skills' => ['Software Testing'],
        'skill_gaps' => [
            ['skill_name' => 'Secure Programming', 'skill_type' => 'technical', 'current_level' => 'Follow', 'current_level_value' => 1, 'required_label' => 'Apply', 'required_level' => 3, 'gap' => 2],
            ['skill_name' => 'Team Communication', 'skill_type' => 'soft', 'current_level' => 'Basic', 'current_level_value' => 1, 'required_label' => 'Advanced', 'required_level' => 3, 'gap' => 2],
        ],
        'development_plan' => ['Practise secure programming.'],
    ]);
});

test('current role titles descriptions and competency gaps render on both plans', function (string $plan) {
    if ($plan === 'premium') {
        UserPlanGrant::create([
            'user_id' => $this->student->id,
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'source' => 'admin',
            'is_active' => true,
        ]);
    }

    $response = $this->actingAs($this->student)
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertOk()
        ->assertSeeText('Analysis Test Engineer')
        ->assertSeeText('Analysis Test Sector')
        ->assertSeeText('Builds reliable software for local businesses.')
        ->assertSee('Secure Programming')
        ->assertSee('Team Communication')
        ->assertDontSeeText('temporary mock values');

    $response->assertSee('id="cpbnGapChart"', false);
})->with(['free', 'premium']);

test('detailed analysis restrictions cannot be bypassed with a direct URL', function () {
    Plan::where('code', 'free')->firstOrFail()->features()
        ->where('key', 'career_recommendations.detailed_analysis.enabled')
        ->update(['value' => false]);

    $this->actingAs($this->student)
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertRedirect(route('student.recommendations.index'));
});

test('disabled detailed analysis sections do not expose their content', function (string $section, string $hidden) {
    FeatureDefinition::where('key', "career_recommendations.detailed_analysis.{$section}.enabled")
        ->update(['global_enabled' => false]);

    $response = $this->actingAs($this->student)
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertOk()
        ->assertDontSee($hidden);

    if ($section === 'match_score') {
        $response->assertDontSee('class="cpbn-metrics"', false)
            ->assertDontSee('data-guide-open="analysis.readiness"', false);
    }
})->with([
    ['match_score', 'Current readiness:'],
    ['matched_competencies', 'Software Testing'],
    ['competency_gaps', 'Secure Programming'],
    ['entry_requirements', 'Entry Requirements'],
    ['certification_suggestions', 'Recommended Certifications'],
    ['training_suggestions', 'Recommended Training'],
    ['development_roadmap', 'Practise secure programming.'],
]);

test('another student cannot view this analysis', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertNotFound();
});

test('legacy career descriptions remain readable', function () {
    $career = BIICFCareer::create([
        'job_title' => 'Legacy Systems Analyst',
        'subsector' => 'Legacy Technology',
        'job_description' => 'Maintains legacy business systems.',
    ]);
    $this->recommendation->update([
        'biicf_job_role_id' => null,
        'biicf_career_id' => $career->id,
    ]);

    $this->actingAs($this->student)
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertOk()
        ->assertSeeText('Legacy Systems Analyst')
        ->assertSeeText('Maintains legacy business systems.');
});

test('the existing chart remains available when advanced analytics is disabled', function () {
    FeatureDefinition::where('key', 'analytics.advanced.enabled')
        ->update(['global_enabled' => false]);

    $this->actingAs($this->student)
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertOk()
        ->assertSee('id="cpbnGapChart"', false)
        ->assertSee('Secure Programming');
});

test('chart failure fallback contains the competency levels', function () {
    $this->actingAs($this->student)
        ->get(route('student.recommendations.analysis', $this->recommendation))
        ->assertOk()
        ->assertSee('id="cpbnGapChartFallback"', false)
        ->assertSeeText('The chart couldn’t load. Please refresh the page.')
        ->assertSeeText('Secure Programming')
        ->assertSeeText('Team Communication')
        ->assertSeeText('Apply')
        ->assertSeeText('Advanced');
});

test('recommendation links follow detailed analysis access', function (string $restriction) {
    if ($restriction === 'plan') {
        Plan::where('code', 'free')->firstOrFail()->features()
            ->where('key', 'career_recommendations.detailed_analysis.enabled')
            ->update(['value' => false]);
    } elseif ($restriction === 'maintenance') {
        FeatureDefinition::where('key', 'career_recommendations.detailed_analysis.enabled')
            ->update(['global_enabled' => false]);
    } elseif ($restriction === 'parent') {
        FeatureDefinition::where('key', 'career_recommendations.enabled')
            ->update(['global_enabled' => false]);
    }

    $response = $this->actingAs($this->student)
        ->get(route('student.recommendations.index'))
        ->assertOk()
        ->assertSeeText('Analysis Test Engineer');

    $link = 'href="'.route('student.recommendations.analysis', $this->recommendation).'"';

    if ($restriction === 'none') {
        $response->assertSee($link, false)
            ->assertDontSeeText('View Career Analysis — Locked');
    } else {
        $response->assertDontSee($link, false)
            ->assertSee('aria-disabled="true"', false)
            ->assertSeeText('View Career Analysis — Locked');

        if ($restriction === 'plan') {
            $response->assertSeeText('Not included in your current plan.');
        } else {
            $response->assertSeeText('Temporarily unavailable due to maintenance.');
        }
    }
})->with(['none', 'plan', 'maintenance', 'parent']);
