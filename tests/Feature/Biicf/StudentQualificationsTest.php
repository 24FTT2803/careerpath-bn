<?php

use App\Models\AcademicRecord;
use App\Models\BiicfJobRole;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;
use Database\Seeders\BiicfReferenceDataSeeder;

/*
 * A student's qualifications, entered on their own profile.
 *
 * The profile used to hold one programme on the user and nothing
 * on the ladder the framework states entry requirements in. A
 * student can hold several — a school certificate, the diploma
 * they are on — and each needs a level before anything can be
 * compared.
 */

function qualificationStudent(): User
{
    return User::factory()->create([
        'role' => 'student',
        'student_id' => '24FTT2803',
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function qualificationPayload(User $student, array $overrides = []): array
{
    return [
        'name' => $student->name,
        'student_id' => $student->student_id,
        'qualifications' => [
            array_merge([
                'institution_name' => 'Politeknik Brunei',
                'programme_name' => 'Diploma in Information Technology',
                'level' => 'Diploma',
                'bdqf_level' => '5',
                'field_of_study' => 'Information Technology',
                'start_date' => now()->subYears(2)->format('Y-m-d'),
                'end_date' => now()->subMonth()->format('Y-m-d'),
                'is_current' => '0',
            ], $overrides),
        ],
    ];
}

beforeEach(function () {
    $this->seed(BiicfReferenceDataSeeder::class);
});

describe('adding a qualification', function () {
    test('a student can record one', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), qualificationPayload($student))
            ->assertSessionHasNoErrors();

        $record = $student->academicRecords()->sole();

        expect($record->programme_name)->toBe('Diploma in Information Technology')
            ->and($record->bdqf_level)->toBe(5)
            ->and($record->isAwarded())->toBeTrue();
    });

    test('several can be held at once', function () {
        /*
         * Which is the point of the table: a school leaving
         * certificate and the diploma on top of it.
         */
        $student = qualificationStudent();

        $payload = qualificationPayload($student);
        $payload['qualifications'][] = [
            'institution_name' => 'Maktab Duli',
            'programme_name' => 'GCE A Level',
            'level' => 'A Level',
            'bdqf_level' => '4',
            'start_date' => now()->subYears(5)->format('Y-m-d'),
            'is_current' => '0',
        ];

        $this->actingAs($student)
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->count())->toBe(2);
    });

    test('a level is optional', function () {
        /*
         * A student who does not know where their award sits
         * says so, and the match reports that rather than
         * comparing against a guess.
         */
        $student = qualificationStudent();

        $this->actingAs($student)
            ->put(
                route('student.profile.update'),
                qualificationPayload($student, ['bdqf_level' => ''])
            )
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->sole()->bdqf_level)->toBeNull();
    });

    test('a level outside the framework\'s ladder is refused', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(
                route('student.profile.update'),
                qualificationPayload($student, ['bdqf_level' => '12'])
            )
            ->assertSessionHasErrors('qualifications.0.bdqf_level');
    });

    test('a programme still being studied is marked as such', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->put(
                route('student.profile.update'),
                qualificationPayload($student, [
                    'is_current' => '1',
                    'end_date' => '',
                ])
            )
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->sole()->isAwarded())->toBeFalse();
    });
});

describe('the form', function () {
    test('it says which way the studying box is set', function () {
        /*
         * The checkbox sits in a field styled for text inputs,
         * which squeezed the native tick out of sight: there was
         * no telling whether it was ticked. The state is now
         * written out beside it.
         */
        $student = qualificationStudent();

        $this->actingAs($student)
            ->put(
                route('student.profile.update'),
                qualificationPayload($student, ['is_current' => '1', 'end_date' => ''])
            );

        $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertSuccessful()
            ->assertSee('cpbn-check-state', escape: false)
            ->assertSee('Still studying this');
    });
});

describe('changing one', function () {
    test('an existing record is updated rather than duplicated', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->put(route('student.profile.update'), qualificationPayload($student));

        $record = $student->academicRecords()->sole();

        $this->actingAs($student)
            ->put(route('student.profile.update'), qualificationPayload($student, [
                'id' => $record->id,
                'bdqf_level' => '6',
                'level' => "Bachelor's Degree",
            ]))
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->count())->toBe(1)
            ->and($record->fresh()->bdqf_level)->toBe(6);
    });

    test('removal is explicit', function () {
        /*
         * A row simply missing from the submission is not
         * authority to delete a record, the same rule
         * certifications follow.
         */
        $student = qualificationStudent();

        $this->actingAs($student)
            ->put(route('student.profile.update'), qualificationPayload($student));

        $record = $student->academicRecords()->sole();

        $this->actingAs($student)
            ->put(route('student.profile.update'), [
                'name' => $student->name,
                'student_id' => $student->student_id,
            ])
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->count())->toBe(1);

        $this->actingAs($student)
            ->put(route('student.profile.update'), [
                'name' => $student->name,
                'student_id' => $student->student_id,
                'removed_qualification_ids' => [$record->id],
            ])
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->count())->toBe(0);
    });

    test('a student cannot edit someone else\'s qualification', function () {
        $student = qualificationStudent();
        $other = User::factory()->create(['role' => 'student']);

        $theirs = AcademicRecord::create([
            'user_id' => $other->id,
            'institution_name' => 'Politeknik Brunei',
            'programme_name' => 'Diploma in Engineering',
            'level' => 'Diploma',
            'bdqf_level' => 5,
            'start_date' => now()->subYears(2),
            'is_current' => false,
        ]);

        $this->actingAs($student)
            ->put(
                route('student.profile.update'),
                qualificationPayload($student, ['id' => $theirs->id])
            )
            ->assertNotFound();

        expect($theirs->fresh()->programme_name)->toBe('Diploma in Engineering');
    });
});

describe('what the explorer then says', function () {
    test('the qualification is recorded, and the role still states its own requirement', function () {
        /*
         * This used to check that the explorer compared the
         * qualification against the role and said "met". It no
         * longer compares: a requirement reading "BDQF Level 2 in
         * IT or related field with relevant industry experience"
         * says what it wants plainly, and the app's reading of it
         * was wrong often enough to be worth less than the
         * sentence.
         *
         * What is still worth pinning is the half this test was
         * really about — that saving a qualification stores its
         * level — and that the role's own requirement survives to
         * the page.
         */
        $this->seed(BiicfJobRoleSeeder::class);

        $student = qualificationStudent();

        $this->actingAs($student)
            ->put(route('student.profile.update'), qualificationPayload($student));

        expect($student->academicRecords()->value('bdqf_level'))->toBe(5);

        $role = BiicfJobRole::where('title', 'Data Entry')->sole();

        $response = $this->actingAs($student)
            ->getJson(route('student.biicf-explorer.job-role.show', $role))
            ->assertSuccessful();

        expect($response->json('tiers.0.entry_requirements'))->not->toBeEmpty()
            ->and($response->json('tiers.0'))->not->toHaveKey('qualification_match');
    });
});
