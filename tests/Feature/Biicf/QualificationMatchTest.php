<?php

use App\Models\AcademicRecord;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\Biicf\QualificationMatcher;
use Database\Seeders\BiicfJobRoleSeeder;
use Illuminate\Support\Str;

/*
 * Whether a student's qualifications open a role.
 *
 * The framework states entry as a BDQF level, usually with two
 * ways in, and asks for things besides — years in the field, an
 * industry certification — that the app holds no record of.
 * What matters here is that the level is compared properly and
 * that nothing else is turned into a verdict.
 */

function matchStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

function qualification(User $student, ?int $level, bool $awarded = true): AcademicRecord
{
    return AcademicRecord::create([
        'user_id' => $student->id,
        'institution_name' => 'Politeknik Brunei',
        'programme_name' => 'Diploma in Information Technology',
        'level' => 'Diploma',
        'bdqf_level' => $level,
        'start_date' => now()->subYears(2),
        'is_current' => ! $awarded,
    ]);
}

function requirementTier(array $alternatives): BiicfJobRoleTier
{
    $role = BiicfJobRole::create([
        'sub_sector_id' => BiicfSubSector::firstOrCreate(
            ['slug' => 'it-services'],
            ['name' => 'IT Services']
        )->id,
        'title' => 'Matching Role '.Str::random(5),
        'slug' => 'matching-role-'.Str::random(5),
    ]);

    $tier = BiicfJobRoleTier::create([
        'job_role_id' => $role->id,
        'label' => 'Entrant',
        'sort_order' => 1,
    ]);

    foreach ($alternatives as $index => $alternative) {
        BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'alternative_number' => $index + 1,
            ...$alternative,
        ]);
    }

    return $tier->fresh('entryRequirements');
}

describe('comparing a level', function () {
    test('a qualification above the level meets it', function () {
        $student = matchStudent();
        qualification($student, 6);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([['bdqf_level' => 5, 'raw_text' => 'BDQF Level 5 in IT']])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::MET)
            ->and($result['held_level'])->toBe(6);
    });

    test('a qualification below the level does not', function () {
        $student = matchStudent();
        qualification($student, 4);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([['bdqf_level' => 6, 'raw_text' => 'BDQF Level 6 in IT']])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::NOT_MET);
    });

    test('meeting either way in opens the tier', function () {
        /*
         * The book separates alternatives with OR, so the lower
         * of the two is what a student needs to clear.
         */
        $student = matchStudent();
        qualification($student, 5);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([
                ['bdqf_level' => 6, 'raw_text' => 'BDQF Level 6 in IT'],
                ['bdqf_level' => 5, 'raw_text' => 'BDQF Level 5 in IT with experience'],
            ])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::MET)
            ->and(collect($result['alternatives'])->pluck('outcome')->all())
            ->toBe([QualificationMatcher::NOT_MET, QualificationMatcher::MET]);
    });
});

describe('what cannot be judged', function () {
    test('a student with no qualification recorded is undetermined', function () {
        /*
         * Not "does not meet": nothing has been said either way,
         * and telling a student they fall short on that basis
         * would be wrong.
         */
        $result = (new QualificationMatcher)->forTier(
            matchStudent(),
            requirementTier([['bdqf_level' => 5, 'raw_text' => 'BDQF Level 5 in IT']])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::UNDETERMINED)
            ->and($result['held_level'])->toBeNull();
    });

    test('a qualification carrying no level is passed over', function () {
        $student = matchStudent();
        qualification($student, null);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([['bdqf_level' => 5, 'raw_text' => 'BDQF Level 5 in IT']])
        );

        expect($result['held_level'])->toBeNull()
            ->and($result['outcome'])->toBe(QualificationMatcher::UNDETERMINED);
    });

    test('a requirement naming no level cannot be judged', function () {
        $student = matchStudent();
        qualification($student, 6);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([[
                'raw_text' => 'Any relevant Project Management Certification with experience',
                'requires_industry_certification' => true,
            ]])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::UNDETERMINED);
    });

    test('experience and certifications are reported, not judged', function () {
        /*
         * A student who clears the level is told they meet it
         * and what else the role asks for, rather than being
         * failed on something the app never sees.
         */
        $student = matchStudent();
        qualification($student, 6);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([[
                'bdqf_level' => 6,
                'raw_text' => 'BDQF Level 6 in IT with 10 years',
                'years_experience' => '10',
                'requires_industry_certification' => true,
            ]])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::MET)
            ->and($result['alternatives'][0]['conditions'])
            ->toBe(['10 years of experience', 'a related industry certification']);
    });

    test('one unjudgeable way in does not refuse the tier', function () {
        /*
         * Undetermined beats not met: an alternative nobody can
         * check should not read as a door closed.
         */
        $student = matchStudent();
        qualification($student, 3);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([
                ['bdqf_level' => 6, 'raw_text' => 'BDQF Level 6 in IT'],
                ['raw_text' => 'Any relevant certification', 'requires_industry_certification' => true],
            ])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::UNDETERMINED);
    });
});

describe('a qualification still being studied', function () {
    test('it does not count as held', function () {
        $student = matchStudent();
        qualification($student, 5, awarded: false);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([['bdqf_level' => 5, 'raw_text' => 'BDQF Level 5 in IT']])
        );

        expect($result['held_level'])->toBeNull()
            ->and($result['studying_level'])->toBe(5)
            ->and($result['outcome'])->toBe(QualificationMatcher::UNDETERMINED);
    });

    test('a student part way through is told what finishing brings', function () {
        $student = matchStudent();
        qualification($student, 3);
        qualification($student, 5, awarded: false);

        $result = (new QualificationMatcher)->forTier(
            $student,
            requirementTier([['bdqf_level' => 5, 'raw_text' => 'BDQF Level 5 in IT']])
        );

        expect($result['outcome'])->toBe(QualificationMatcher::NOT_MET)
            ->and($result['alternatives'][0]['met_on_finishing'])->toBeTrue();
    });
});

describe('the explorer', function () {
    /*
     * These two used to pin that a role's tiers carried a
     * verdict: met where the student's level cleared the tier,
     * not met where it did not. The page no longer says anything
     * of the kind, so what they pinned is gone — the book's own
     * sentence says what is wanted, and a student reads it.
     *
     * What is worth holding is that the panel keeps the
     * requirement itself, and keeps no opinion about it.
     */
    test('a role carries each tier\'s requirements and no verdict on them', function () {
        $this->seed(BiicfJobRoleSeeder::class);

        $student = matchStudent();
        qualification($student, 4);

        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $response = $this->actingAs($student)
            ->getJson(route('student.biicf-explorer.job-role.show', $role))
            ->assertSuccessful();

        $tiers = $response->json('tiers');

        expect($tiers)->toHaveCount(3);

        foreach ($tiers as $tier) {
            expect($tier)->not->toHaveKey('qualification_match')
                ->and($tier['entry_requirements'])->not->toBeEmpty();
        }

        /*
         * The book's wording, whole, which is the thing the
         * student is there to read.
         */
        expect($tiers[0]['entry_requirements'][0]['text'])
            ->toContain('BDQF Level');
    });
});
