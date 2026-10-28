<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\AI\CareerAiPayloadBuilder;
use App\Services\AI\CareerRecommendationContextBuilder;
use App\Services\AI\CareerRecommendationEnricher;
use App\Services\AI\CareerRecommendationService;
use App\Services\AI\MockCareerAiClient;
use App\Services\AI\ProfileSnapshotService;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Support\Str;

/*
 * The offline driver on the current schema.
 *
 * It used to answer in schema 1.0, against the five rows in
 * biicf_careers, so which dataset a student's recommendations
 * came from depended on whether CAREER_AI_DRIVER said groq.
 * Nothing on the page distinguished the two: a student could be
 * shown three careers with no job role, no tier and no BIICF
 * competency behind them, and it looked exactly like a real set.
 *
 * These cover the join rather than the mock's taste in roles —
 * that it answers in 2.0, that the service believes it, and that
 * what lands in the database is a job role with the framework's
 * own competency analysis against it.
 */

function frameworkRole(
    string $title,
    int $pathLevel,
    array $competencies,
    string $subSectorName = 'Software Development'
): BiicfJobRole {
    $subSector = BiicfSubSector::firstOrCreate(
        ['slug' => Str::slug($subSectorName)],
        ['name' => $subSectorName]
    );

    $role = BiicfJobRole::create([
        'sub_sector_id' => $subSector->id,
        'title' => $title,
        'slug' => Str::slug($title),
        'career_path_level' => $pathLevel,
    ]);

    $tier = BiicfJobRoleTier::create([
        'job_role_id' => $role->id,
        'label' => 'Level 1',
        'sort_order' => 1,
    ]);

    foreach ($competencies as $name => $levelNumber) {
        $competency = BiicfCompetency::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name, 'type' => 'technical']
        );

        /*
         * Attached through the tier, which fills the role in on
         * the pivot itself.
         */
        $tier->competencies()->attach($competency->id, [
            'proficiency_level_id' => BiicfProficiencyLevel::technical()
                ->where('level_number', $levelNumber)
                ->sole()
                ->id,

            'is_core' => true,
        ]);
    }

    return $role->fresh();
}

/**
 * Three roles wanting different things, so a profile can prefer
 * one of them for a reason.
 *
 * @return array<string, BiicfJobRole>
 */
function miniFramework(): array
{
    return [
        'developer' => frameworkRole('Application Developer', 0, [
            'Programming' => 3,
            'Software Testing' => 2,
        ]),

        'network' => frameworkRole('Network Administrator', 0, [
            'Network Administration' => 3,
            'Systems Monitoring' => 2,
        ], 'Infrastructure'),

        'analyst' => frameworkRole('Data Analyst', 0, [
            'Data Analysis' => 3,
            'Statistical Reporting' => 2,
        ], 'Data and Analytics'),
    ];
}

function studentWhoCan(array $skills): User
{
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student->profile()->create([]);

    foreach ($skills as $name => $level) {
        $student->competencies()->create([
            'skill_name' => $name,
            'category' => 'technical',
            'proficiency_level' => $level,
        ]);
    }

    return $student->fresh();
}

function mockDrivenService(): CareerRecommendationService
{
    config(['career-ai.driver' => 'mock']);

    $payloadBuilder = new CareerAiPayloadBuilder;

    return new CareerRecommendationService(
        new MockCareerAiClient,
        $payloadBuilder,
        new CareerRecommendationContextBuilder($payloadBuilder),
        new CareerRecommendationEnricher,
        new ProfileSnapshotService($payloadBuilder)
    );
}

beforeEach(function () {
    $this->seed(BiicfReferenceDataSeeder::class);
});

test('the offline driver recommends job roles, not legacy careers', function () {
    miniFramework();

    $recommendations = mockDrivenService()->generateFor(
        studentWhoCan(['Programming' => 'Apply'])
    );

    expect($recommendations)->toHaveCount(3);

    foreach ($recommendations as $recommendation) {
        expect($recommendation->biicf_job_role_id)
            ->not->toBeNull()
            ->and($recommendation->biicf_career_id)
            ->toBeNull()
            ->and($recommendation->jobRole)
            ->not->toBeNull();
    }
});

test('the generation records the schema it was built on', function () {
    /*
     * What distinguishes a set generated against the framework
     * from one generated against the five careers, after the
     * fact. Reading the driver name would no longer tell anyone:
     * "mock" means 1.0 before this change and 2.0 after it.
     */
    miniFramework();

    $recommendations = mockDrivenService()->generateFor(
        studentWhoCan(['Programming' => 'Apply'])
    );

    expect($recommendations->first()->generation->schema_version)
        ->toBe('2.0')
        ->and($recommendations->first()->generation->driver)
        ->toBe('mock');
});

test('the framework decides the ranking, and says why', function () {
    $roles = miniFramework();

    $recommendations = mockDrivenService()->generateFor(
        studentWhoCan([
            'Network Administration' => 'Apply',
            'Systems Monitoring' => 'Assist',
        ])
    );

    $top = $recommendations->firstWhere('rank', 1);

    expect($top->biicf_job_role_id)
        ->toBe($roles['network']->id)
        ->and($top->explanation)
        ->toContain('Network Administrator')
        ->and($top->match_score)
        ->toBeGreaterThan(
            $recommendations->firstWhere('rank', 3)->match_score
        );
});

test('the competency analysis is the framework\'s, not the mock\'s', function () {
    /*
     * The mock sends no skill gaps and no readiness score at all.
     * Both are computed by the enricher from the role's own
     * tiers, which is the half of a recommendation that has to be
     * right whichever driver produced it.
     */
    miniFramework();

    $recommendations = mockDrivenService()->generateFor(
        studentWhoCan(['Programming' => 'Apply'])
    );

    $developer = $recommendations
        ->first(
            fn ($recommendation) => $recommendation->jobRole->title
                === 'Application Developer'
        );

    expect($developer->matched_skills)
        ->toContain('Programming')
        ->and($developer->career_readiness_score)
        ->toBeGreaterThan(0)
        ->and(collect($developer->skill_gaps)->pluck('skill_name'))
        /* Asked for at level 2 and not recorded at all. */
        ->toContain('Software Testing');
});

test('the same profile generates the same three roles', function () {
    /*
     * A student regenerating without changing anything should not
     * watch their matches reshuffle. Roles the student matches
     * equally well are ordered by the framework's own career path
     * level and then by id, so the order is a property of the
     * data rather than of the row order a query happened to
     * return.
     */
    miniFramework();

    $student = studentWhoCan(['Programming' => 'Apply']);

    $service = mockDrivenService();

    $first = $service->generateFor($student)
        ->sortBy('rank')
        ->pluck('biicf_job_role_id')
        ->all();

    $second = $service->generateFor($student->fresh())
        ->sortBy('rank')
        ->pluck('biicf_job_role_id')
        ->all();

    expect($second)->toBe($first);
});

test('a framework with fewer than three profiled roles is refused', function () {
    /*
     * Rather than returning two, or inventing a third. A
     * generation that cannot be made is left unmade: the
     * student's existing set stays current.
     */
    frameworkRole('Application Developer', 0, ['Programming' => 3]);
    frameworkRole('Network Administrator', 0, ['Network Administration' => 3]);

    $student = studentWhoCan(['Programming' => 'Apply']);

    expect(fn () => mockDrivenService()->generateFor($student))
        ->toThrow(RuntimeException::class);

    expect($student->recommendationGenerations()->count())->toBe(0);
});
