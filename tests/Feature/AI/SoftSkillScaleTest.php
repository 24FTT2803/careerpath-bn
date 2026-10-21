<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\AI\CareerRecommendationEnricher;
use Database\Seeders\BiicfReferenceDataSeeder;

/*
 * Measuring a student against a soft skill.
 *
 * The framework rates soft skills on four named rungs and
 * technical competencies on five, and a number means nothing
 * without knowing which. Read on the wrong one, a student who
 * has exactly what a role asks for is told they fall short of
 * it — which is the one thing this application exists to get
 * right.
 */

beforeEach(function () {
    /*
     * The scales and their rungs, which is all this needs. The
     * competency library is seeded from a four-hundred-kilobyte
     * file and none of these tests care what is in it, so the
     * one competency each needs is made here instead.
     */
    $this->seed(BiicfReferenceDataSeeder::class);
});

function competencyOfType(string $type): BiicfCompetency
{
    return BiicfCompetency::create([
        'name' => $type === 'soft_skill' ? 'Test Soft Skill' : 'Test Technical Competency',
        'slug' => $type === 'soft_skill' ? 'test-soft-skill' : 'test-technical-competency',
        'type' => $type,
    ]);
}

function roleRequiring(BiicfCompetency $competency, BiicfProficiencyLevel $level): BiicfJobRole
{
    $role = BiicfJobRole::create([
        'title' => 'Test Role',
        'slug' => 'test-role',
        'sub_sector_id' => BiicfSubSector::create([
            'name' => 'Test Sub-Sector',
            'slug' => 'test-sub-sector',
        ])->id,
    ]);

    $tier = BiicfJobRoleTier::create([
        'job_role_id' => $role->id,
        'label' => 'Specialist',
        'sort_order' => 1,
    ]);

    $tier->competencies()->attach($competency->id, [
        'job_role_id' => $role->id,
        'proficiency_level_id' => $level->id,
        'is_core' => true,
    ]);

    return $role->fresh();
}

function enrichedFor(User $student, BiicfJobRole $role): array
{
    return (new CareerRecommendationEnricher)->enrich($student, [
        'biicf_job_role_id' => $role->id,
    ]);
}

test('a soft skill the student already has is not reported as a gap', function () {
    /*
     * Advanced is the third of a soft skill's four rungs. Read
     * against the technical words — Follow, Assist, Apply — it
     * is not a word at all, and the student came out at nothing.
     */
    $competency = competencyOfType('soft_skill');

    $advanced = BiicfProficiencyLevel::where('scale', BiicfProficiencyLevel::SCALE_SOFT)
        ->where('level_number', 3)
        ->firstOrFail();

    $role = roleRequiring($competency, $advanced);

    $student = User::factory()->create(['role' => 'student']);

    $student->competencies()->create([
        'skill_name' => $competency->name,
        'category' => 'soft',
        'proficiency_level' => $advanced->name,
    ]);

    $enriched = enrichedFor($student, $role);

    expect($enriched['skill_gaps'])->toBe([])
        ->and($enriched['matched_skills'])->toContain($competency->name);
});

test('a soft skill requirement is named on its own scale', function () {
    /*
     * The third rung of a soft skill is "Advanced", not "Apply".
     * Labelling it with the technical word tells a student to
     * reach something the book never asked of them.
     */
    $competency = competencyOfType('soft_skill');

    $advanced = BiicfProficiencyLevel::where('scale', BiicfProficiencyLevel::SCALE_SOFT)
        ->where('level_number', 3)
        ->firstOrFail();

    $student = User::factory()->create(['role' => 'student']);

    $enriched = enrichedFor($student, roleRequiring($competency, $advanced));

    expect($enriched['skill_gaps'])->toHaveCount(1)
        ->and($enriched['skill_gaps'][0]['required_label'])->toBe($advanced->name)
        ->and($enriched['skill_gaps'][0]['required_label'])->not->toBe('Apply');
});

test('a technical requirement is still named on the technical scale', function () {
    $competency = competencyOfType('technical');

    $apply = BiicfProficiencyLevel::where('scale', BiicfProficiencyLevel::SCALE_TECHNICAL)
        ->where('level_number', 3)
        ->firstOrFail();

    $student = User::factory()->create(['role' => 'student']);

    $enriched = enrichedFor($student, roleRequiring($competency, $apply));

    expect($enriched['skill_gaps'][0]['required_label'])->toBe($apply->name);
});

test('a soft skill one rung short is one rung short, not two', function () {
    /*
     * Intermediate against a requirement of Advanced is a gap of
     * one on a four-rung scale. Measured on the five-rung one it
     * came out as a bigger shortfall than the book describes.
     */
    $competency = competencyOfType('soft_skill');

    $soft = BiicfProficiencyLevel::where('scale', BiicfProficiencyLevel::SCALE_SOFT);

    $advanced = (clone $soft)->where('level_number', 3)->firstOrFail();
    $intermediate = (clone $soft)->where('level_number', 2)->firstOrFail();

    $student = User::factory()->create(['role' => 'student']);

    $student->competencies()->create([
        'skill_name' => $competency->name,
        'category' => 'soft',
        'proficiency_level' => $intermediate->name,
    ]);

    $enriched = enrichedFor($student, roleRequiring($competency, $advanced));

    expect($enriched['skill_gaps'])->toHaveCount(1)
        ->and($enriched['skill_gaps'][0]['gap'])->toBe(1)
        ->and($enriched['skill_gaps'][0]['current_level'])->toBe($intermediate->name);
});
