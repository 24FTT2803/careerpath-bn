<?php

use App\Models\AcademicRecord;
use App\Models\BiicfCompetency;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobLevel;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Models\CareerRecommendation;
use App\Models\RecommendationGeneration;
use App\Models\StudentMilestone;
use App\Models\User;
use App\Services\AI\CareerAdviserContextBuilder;
use App\Services\Biicf\JobRoleContentUpdater;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Support\Str;

/*
 * What the Career Adviser is given to answer from.
 *
 * It is told never to state as fact anything this context does
 * not hold — and the context held role titles and competency
 * names and nothing that says what a role asks for. Asked what a
 * BDQF level is, whether they could enter a role, or how to get
 * from one role to another, the honest answer was that it did not
 * know, and that is the answer students were getting. Nor did it
 * hold their milestones.
 *
 * These check what it holds now, and that the full detail stays
 * confined to the student's own recommended roles: sending every
 * role in full would add around sixty thousand tokens to every
 * question.
 */

beforeEach(function () {
    $this->seed(BiicfReferenceDataSeeder::class);
});

function adviserContextFor(User $student): array
{
    return app(CareerAdviserContextBuilder::class)->build($student);
}

function adviserStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

/**
 * A role as the book states one: a tier at a named level, a way
 * in at a BDQF level, a competency at a level, a training, and
 * somewhere it leads.
 */
function roleAsTheBookStatesIt(string $title = 'Application Developer'): BiicfJobRole
{
    $subSector = BiicfSubSector::firstOrCreate(
        ['slug' => 'applications-and-solutions-development'],
        ['name' => 'Applications and Solutions Development']
    );

    $role = BiicfJobRole::create([
        'sub_sector_id' => $subSector->id,
        'title' => $title,
        'slug' => Str::slug($title),
    ]);

    (new JobRoleContentUpdater)->replaceDescription(
        $role,
        'Builds and maintains applications.'
    );

    $entrant = BiicfJobLevel::where('name', 'Entrant')->sole();

    $role->jobLevels()->attach($entrant->id);

    $tier = BiicfJobRoleTier::create([
        'job_role_id' => $role->id,
        'label' => 'Entrant',
        'sort_order' => 1,
        'job_level_id' => $entrant->id,
    ]);

    BiicfEntryRequirement::create([
        'job_role_tier_id' => $tier->id,
        'alternative_number' => 1,
        'bdqf_level' => 5,
        'field_of_study' => 'IT or related field',
        'raw_text' => 'BDQF Level 5 in IT or related field.',
    ]);

    $competency = BiicfCompetency::create([
        'name' => 'Application Development',
        'slug' => 'application-development',
        'type' => 'technical',
    ]);

    $tier->competencies()->attach($competency->id, [
        'proficiency_level_id' => BiicfProficiencyLevel::technical()
            ->where('level_number', 3)
            ->sole()
            ->id,
        'is_core' => true,
    ]);

    $role->trainings()->attach(
        BiicfTraining::create(['name' => 'Secure Coding Fundamentals'])->id
    );

    $next = BiicfJobRole::create([
        'sub_sector_id' => $subSector->id,
        'title' => 'Solution Architect',
        'slug' => 'solution-architect',
    ]);

    $role->progressesTo()->attach($next->id);

    return $role->fresh();
}

function recommendRoleTo(User $student, BiicfJobRole $role): void
{
    $generation = RecommendationGeneration::create([
        'user_id' => $student->id,
        'generation_number' => 1,
        'status' => RecommendationGeneration::STATUS_CURRENT,
        'driver' => 'mock',
        'schema_version' => '2.0',
        'recommendation_count' => 1,
        'generated_at' => now(),
    ]);

    CareerRecommendation::create([
        'user_id' => $student->id,
        'recommendation_generation_id' => $generation->id,
        'biicf_job_role_id' => $role->id,
        'rank' => 1,
        'match_score' => 88,
        'career_readiness_score' => 60,
        'matched_skills' => [],
        'skill_gaps' => [],
        'development_plan' => ['One.', 'Two.', 'Three.'],
        'explanation' => 'A fit.',
    ]);
}

describe('milestones', function () {
    test('the adviser holds the student\'s milestones', function () {
        $student = adviserStudent();

        StudentMilestone::create([
            'user_id' => $student->id,
            'title' => 'Finish my CCNA',
            'category' => 'skill',
            'target_date' => now()->addMonth()->toDateString(),
            'priority' => 2,
            'subtasks' => ['Book the exam'],
        ]);

        $milestones = adviserContextFor($student)['student_milestones'];

        expect($milestones)->toHaveCount(1)
            ->and($milestones[0]['title'])->toBe('Finish my CCNA')
            ->and($milestones[0]['is_completed'])->toBeFalse()
            ->and($milestones[0]['subtasks'])->toBe(['Book the exam'])
            ->and($milestones[0]['has_proof'])->toBeFalse();
    });

    test('open ones come first, soonest first', function () {
        $student = adviserStudent();

        foreach ([
            ['Done already', true, 10],
            ['Later', false, 60],
            ['Soon', false, 5],
        ] as [$title, $done, $days]) {
            StudentMilestone::create([
                'user_id' => $student->id,
                'title' => $title,
                'category' => 'career',
                'target_date' => now()->addDays($days)->toDateString(),
                'is_completed' => $done,
            ]);
        }

        expect(collect(adviserContextFor($student)['student_milestones'])->pluck('title')->all())
            ->toBe(['Soon', 'Later', 'Done already']);
    });

    test('it is given today, to say what is overdue', function () {
        /*
         * The model has no clock. Without this a target date is
         * a date and nothing more.
         */
        expect(adviserContextFor(adviserStudent())['today'])
            ->toBe(now()->toDateString());
    });
});

describe('qualifications and the BDQF', function () {
    test('each qualification carries its BDQF level', function () {
        /*
         * It reached the adviser without one, so the adviser could
         * read what a role asked for and not say whether the
         * student had it.
         */
        $student = adviserStudent();

        AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Politeknik Brunei',
            'programme_name' => 'Diploma in ICT (Application Development)',
            'level' => 'Advanced Diploma',
            'bdqf_level' => 5,
            'start_date' => now()->subYears(2)->toDateString(),
            'is_current' => true,
            'is_programme_record' => true,
        ]);

        $record = adviserContextFor($student)['student_profile']['academic_records'][0];

        expect($record['bdqf_level'])->toBe(5)
            ->and($record['award'])->toBe('Advanced Diploma')
            ->and($record['status'])->toBe('still studying')
            ->and($record['is_their_politeknik_programme'])->toBeTrue();
    });

    test('a qualification still being studied is not counted as held', function () {
        $student = adviserStudent();

        AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'A school',
            'programme_name' => 'A-Levels',
            'level' => 'A-Level',
            'bdqf_level' => 3,
            'start_date' => now()->subYears(4)->toDateString(),
            'end_date' => now()->subYears(2)->toDateString(),
            'is_current' => false,
        ]);

        AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Politeknik Brunei',
            'programme_name' => 'Diploma in ICT (Data Analytics)',
            'level' => 'Advanced Diploma',
            'bdqf_level' => 5,
            'start_date' => now()->subYear()->toDateString(),
            'is_current' => true,
        ]);

        $summary = adviserContextFor($student)['student_qualification_summary'];

        expect($summary['highest_awarded_bdqf_level'])->toBe(3)
            ->and($summary['studying_towards_bdqf_level'])->toBe(5);
    });

    test('an unrecorded level is counted rather than guessed', function () {
        $student = adviserStudent();

        AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Somewhere',
            'programme_name' => 'Something',
            'level' => 'Certificate',
            'start_date' => now()->subYears(3)->toDateString(),
            'is_current' => false,
        ]);

        $summary = adviserContextFor($student)['student_qualification_summary'];

        expect($summary['highest_awarded_bdqf_level'])->toBeNull()
            ->and($summary['qualifications_without_a_bdqf_level'])->toBe(1);
    });

    test('the BDQF ladder is supplied, by its own name', function () {
        $bdqf = adviserContextFor(adviserStudent())['biicf_reference']['bdqf'];

        expect($bdqf['name'])->toBe('Brunei Darussalam Qualifications Framework')
            ->and($bdqf['levels'])->not->toBeEmpty();

        $five = collect($bdqf['levels'])->firstWhere('level', 5);

        expect($five['technical_and_vocational'])->toContain('Advanced Diploma');
    });

    test('both proficiency scales and the job levels are supplied', function () {
        $reference = adviserContextFor(adviserStudent())['biicf_reference'];

        expect($reference['proficiency_scales'])->toHaveKeys(['technical', 'soft'])
            ->and(collect($reference['proficiency_scales']['technical'])->pluck('name')->all())
            ->toContain('Apply')
            ->and($reference['job_levels'])->toContain('Entrant');
    });
});

describe('the student\'s recommended roles', function () {
    test('arrive in full', function () {
        $student = adviserStudent();

        $role = roleAsTheBookStatesIt();

        recommendRoleTo($student, $role);

        $inFull = adviserContextFor($student)['career_recommendations'][0]['job_role'];

        $tier = $inFull['tiers'][0];

        expect($inFull['job_levels'])->toBe(['Entrant'])
            ->and($tier['job_level'])->toBe('Entrant')
            ->and($tier['entry_requirements'][0]['as_written'])
            ->toBe('BDQF Level 5 in IT or related field.')
            ->and($tier['entry_requirements'][0]['bdqf_level'])->toBe(5)
            ->and($inFull['trainings'])->toBe(['Secure Coding Fundamentals'])
            ->and($inFull['leads_to'])->toBe(['Solution Architect']);
    });

    test('say the level each competency is wanted at, on its own scale', function () {
        /*
         * "3" on its own means nothing: it is Apply on the
         * technical scale's five and Advanced on the soft scale's
         * four, and those are not the same requirement.
         */
        $student = adviserStudent();

        recommendRoleTo($student, roleAsTheBookStatesIt());

        $competency = adviserContextFor($student)['career_recommendations'][0]['job_role']['tiers'][0]['competencies'][0];

        expect($competency['name'])->toBe('Application Development')
            ->and($competency['required_level'])->toMatchArray([
                'name' => 'Apply',
                'level' => 3,
                'out_of' => 5,
                'scale' => 'technical',
            ]);
    });

    test('read their description from its rows', function () {
        /*
         * The catalogue beside them already did. The recommended
         * role read the old column, which stops being the truth
         * once the book is imported.
         */
        $student = adviserStudent();

        $role = roleAsTheBookStatesIt();

        $role->forceFill(['job_description' => null])->save();

        recommendRoleTo($student, $role);

        expect(adviserContextFor($student)['career_recommendations'][0]['job_role']['job_description'])
            ->toBe('Builds and maintains applications.');
    });
});

describe('every other role', function () {
    test('arrives as a summary of levels, entry and progression', function () {
        roleAsTheBookStatesIt();

        $summary = collect(adviserContextFor(adviserStudent())['biicf_reference']['job_roles'])
            ->firstWhere('title', 'Application Developer');

        expect($summary['job_levels'])->toBe(['Entrant'])
            ->and($summary['entry_bdqf_levels'])->toBe([5])
            ->and($summary['leads_to'])->toBe(['Solution Architect']);
    });

    test('without the full table, which stays with the recommended ones', function () {
        /*
         * The budget the whole design rests on. Every role in full
         * is around sixty thousand tokens on every question.
         */
        roleAsTheBookStatesIt();

        $summary = collect(adviserContextFor(adviserStudent())['biicf_reference']['job_roles'])
            ->firstWhere('title', 'Application Developer');

        expect($summary)->not->toHaveKey('tiers')
            ->not->toHaveKey('trainings');
    });

    test('a role nothing leads to or from says so with empty lists', function () {
        roleAsTheBookStatesIt();

        $architect = collect(adviserContextFor(adviserStudent())['biicf_reference']['job_roles'])
            ->firstWhere('title', 'Solution Architect');

        expect($architect['leads_to'])->toBe([])
            ->and($architect['comes_from'])->toBe(['Application Developer'])
            ->and($architect['entry_bdqf_levels'])->toBe([]);
    });
});

test('a student with nothing yet still gets the whole framework', function () {
    $context = adviserContextFor(adviserStudent());

    expect($context['career_recommendations'])->toBe([])
        ->and($context['student_milestones'])->toBe([])
        ->and($context['student_qualification_summary']['highest_awarded_bdqf_level'])->toBeNull()
        ->and($context['biicf_reference'])->toHaveKeys([
            'bdqf',
            'proficiency_scales',
            'job_levels',
            'job_roles',
            'competencies',
        ]);
});
