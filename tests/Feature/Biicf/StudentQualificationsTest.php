<?php

use App\Models\AcademicRecord;
use App\Models\BiicfJobRole;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
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
            ->assertShows('Still studying this');
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

describe('a programme still being studied', function () {
    test('it is saved without a date it finished', function () {
        /*
         * A date it finished contradicts still being on it. The
         * form hides the field once the box is ticked; this is
         * what holds when a request arrives anyway, and what
         * clears a date a student typed before ticking it.
         */
        $student = qualificationStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), qualificationPayload($student, [
                'is_current' => '1',
                'end_date' => now()->subMonth()->format('Y-m-d'),
            ]))
            ->assertSessionHasNoErrors();

        $record = $student->academicRecords()->sole();

        expect($record->end_date)->toBeNull()
            ->and($record->is_current)->toBeTrue()
            ->and($record->isAwarded())->toBeFalse();
    });

    test('one that has finished keeps its date', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), qualificationPayload($student))
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->sole()->end_date)->not->toBeNull();
    });

    test('ticking the box later clears the date already held', function () {
        /*
         * The record is updated rather than replaced, so the
         * stored date has to be cleared rather than simply not
         * written.
         */
        $student = qualificationStudent();

        $record = AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Politeknik Brunei',
            'programme_name' => 'Diploma in Information Technology',
            'level' => 'Diploma',
            'bdqf_level' => 5,
            'start_date' => now()->subYears(2)->format('Y-m-d'),
            'end_date' => now()->subMonth()->format('Y-m-d'),
            'is_current' => false,
        ]);

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), qualificationPayload($student, [
                'id' => $record->id,
                'is_current' => '1',
                'end_date' => now()->subMonth()->format('Y-m-d'),
            ]))
            ->assertSessionHasNoErrors();

        expect($record->fresh()->end_date)->toBeNull();
    });

    test('the form gives the script what it needs to put the field away', function () {
        /*
         * Which it does on load, so the markup itself stays the
         * same for a current programme and a finished one. What
         * the page has to carry is the two hooks: the field to
         * hide and the box that decides.
         */
        $student = qualificationStudent();

        AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Politeknik Brunei',
            'programme_name' => 'Diploma in Information Technology',
            'level' => 'Diploma',
            'bdqf_level' => 5,
            'start_date' => now()->subYears(2)->format('Y-m-d'),
            'is_current' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows(
                'class="cpbn-field" data-finished-field',
                'data-still-studying'
            );
    });
});

/**
 * A programme the form will accept.
 *
 * The programme select is checked against the organisation's own
 * groups, so a name nobody offers cannot be saved at all.
 */
function offeredQualificationProgramme(string $name): string
{
    $organisation = Organisation::firstOrCreate(
        ['code' => 'QUALS-TEST'],
        ['name' => 'Qualifications Test Institute']
    );

    $type = OrganisationGroupType::firstOrCreate(
        ['organisation_id' => $organisation->id, 'name' => 'Programme']
    );

    OrganisationGroup::firstOrCreate(
        ['organisation_id' => $organisation->id, 'name' => $name],
        ['group_type_id' => $type->id, 'code' => 'QUALS-PROG']
    );

    return $name;
}

/**
 * What the form posts for the programme row: the programme select
 * at the top level, and the row keyed so it identifies itself.
 *
 * @return array<string, mixed>
 */
function programmeRowPayload(User $student, array $overrides = []): array
{
    return [
        'name' => $student->name,
        'student_id' => $student->student_id,
        'programme' => offeredQualificationProgramme('Diploma in Information Technology'),
        'cgpa' => '3.40',
        'qualifications' => [
            'programme' => array_merge([
                'is_programme' => '1',
                'level' => 'Diploma',
                'bdqf_level' => '5',
                'start_date' => now()->subYears(2)->format('Y-m-d'),
                'is_current' => '1',
            ], $overrides),
        ],
    ];
}

describe('the programme as a qualification', function () {
    test('Current Studies is gone from the form', function () {
        /*
         * It asked a post-graduate, who has none, to fill in a
         * section built around having some — and it held the
         * programme a second time, beside the qualification that
         * already recorded it.
         */
        $student = qualificationStudent();

        $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertHides('id="ep-studies"')
            ->assertHides('Current Studies');
    });

    test('the programme row is offered to a student with no records', function () {
        /*
         * Pre-filled as far as it can be: the institution is not
         * a question, and the award and the level follow from it.
         */
        $student = qualificationStudent();

        expect($student->academicRecords()->count())->toBe(0);

        $response = $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk();

        $response->assertShows(
            'is-programme-row',
            'Your programme',
            'name="qualifications[programme][is_programme]"',
            'name="qualifications[programme][level]"',
            /*
             * The award PB actually gives, which Annex 1 puts at
             * BDQF level 5 alongside the HND. "Diploma" was the
             * first guess and named the rung below it.
             */
            'value="Advanced Diploma"',
            'name="cgpa"'
        );
    });

    test('the institution is stated rather than asked for', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertShows('class="cpbn-fixed-value"')
            ->assertHides('name="qualifications[programme][institution_name]"');
    });

    test('saving it records the programme against Politeknik Brunei', function () {
        $student = qualificationStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), programmeRowPayload($student))
            ->assertSessionHasNoErrors();

        $record = $student->academicRecords()->sole();

        expect($record->institution_name)->toBe('Politeknik Brunei')
            ->and($record->programme_name)->toBe('Diploma in Information Technology')
            ->and($record->bdqf_level)->toBe(5)
            ->and($record->is_programme_record)->toBeTrue()
            ->and($record->is_current)->toBeTrue();
    });

    test('the CGPA lands on the account as well as the record', function () {
        /*
         * users.cgpa is what the rest of the application reads —
         * profile completion, the readiness score, the payload
         * sent for recommendations.
         */
        $student = qualificationStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), programmeRowPayload($student))
            ->assertSessionHasNoErrors();

        expect((float) $student->fresh()->cgpa)->toBe(3.4)
            ->and((float) $student->academicRecords()->sole()->cgpa)->toBe(3.4);
    });

    test('the programme row needs no programme name of its own', function () {
        /*
         * It takes it from the select, which is still the field
         * that writes users.programme. Posting a second copy
         * would be two things to keep in step.
         */
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);

        expect($payload['qualifications']['programme'])
            ->not->toHaveKey('programme_name');

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasNoErrors();

        expect($student->fresh()->programme)
            ->toBe('Diploma in Information Technology');
    });

    test('a student who has named no programme gets no empty record', function () {
        /*
         * The row is on the form from the start, so it is posted
         * whether or not anything has been chosen in it.
         */
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);
        $payload['programme'] = '';

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->count())->toBe(0);
    });

    test('only one record is ever the programme', function () {
        /*
         * Otherwise the form picks whichever came back first, and
         * which qualification counts as the student's programme
         * changes between page loads.
         */
        $student = qualificationStudent();

        $stale = AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Politeknik Brunei',
            'programme_name' => 'An older programme',
            'level' => 'Diploma',
            'start_date' => now()->subYears(5)->format('Y-m-d'),
            'is_current' => false,
            'is_programme_record' => true,
        ]);

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), programmeRowPayload($student))
            ->assertSessionHasNoErrors();

        expect($stale->fresh()->is_programme_record)->toBeFalse()
            ->and($student->academicRecords()
                ->where('is_programme_record', true)
                ->count())
            ->toBe(1);
    });

    test('an ordinary qualification still needs its own programme name', function () {
        /*
         * Only the programme row is excused, and only because the
         * select above supplies it.
         */
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);

        $payload['qualifications'][0] = [
            'institution_name' => 'Universiti Brunei Darussalam',
            'level' => 'Degree',
            'start_date' => now()->subYears(4)->format('Y-m-d'),
            'is_current' => '0',
        ];

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasErrors('qualifications.0.programme_name');
    });

    test('the programme row carries no Remove button', function () {
        /*
         * The other rows keep theirs, so what matters is that
         * this one has none — read between the start of the
         * pinned card and the start of the next card.
         */
        $student = qualificationStudent();

        AcademicRecord::create([
            'user_id' => $student->id,
            'institution_name' => 'Universiti Brunei Darussalam',
            'programme_name' => 'Bachelor of Computing',
            'level' => 'Degree',
            'start_date' => now()->subYears(4)->format('Y-m-d'),
            'is_current' => false,
        ]);

        $html = $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->getContent();

        $cardStart = mb_strpos($html, 'is-programme-row');

        expect($cardStart)->not->toBeFalse();

        $nextCard = mb_strpos(
            $html,
            'cpbn-certification-card',
            $cardStart + mb_strlen('is-programme-row')
        );

        expect($nextCard)->not->toBeFalse();

        $programmeCard = mb_substr(
            $html,
            $cardStart,
            $nextCard - $cardStart
        );

        expect($programmeCard)->not->toContain('cpbn-remove-qualification');

        /* And the row that is the student's own still has one. */
        expect($html)->toContain('cpbn-remove-qualification');
    });
});

describe('what the programme row is excused', function () {
    test('it posts no institution and no programme name of its own', function () {
        /*
         * Both are required of every other row. This one states
         * its institution on the form and takes its name from the
         * programme select, so requiring either would fail a
         * student on fields the form never showed them — which is
         * what it did.
         */
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);

        expect($payload['qualifications']['programme'])
            ->not->toHaveKey('institution_name')
            ->not->toHaveKey('programme_name');

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->sole()->institution_name)
            ->toBe('Politeknik Brunei');
    });

    test('it saves without a start date, falling back to this year', function () {
        /*
         * The row exists from the moment the account does, so
         * requiring a date here would stop a student saving their
         * interests until they had dated their diploma.
         */
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);
        unset($payload['qualifications']['programme']['start_date']);
        $payload['qualifications']['programme']['start_date'] = '';

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasNoErrors();

        expect($student->academicRecords()->sole()->start_date->format('Y-m-d'))
            ->toBe(now()->startOfYear()->format('Y-m-d'));
    });

    test('an ordinary row still needs its institution', function () {
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);

        $payload['qualifications'][0] = [
            'programme_name' => 'Bachelor of Computing',
            'level' => 'Degree',
            'start_date' => now()->subYears(4)->format('Y-m-d'),
            'is_current' => '0',
        ];

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasErrors('qualifications.0.institution_name');
    });

    test('an ordinary row still needs its start date', function () {
        $student = qualificationStudent();

        $payload = programmeRowPayload($student);

        $payload['qualifications'][0] = [
            'institution_name' => 'Universiti Brunei Darussalam',
            'programme_name' => 'Bachelor of Computing',
            'level' => 'Degree',
            'is_current' => '0',
        ];

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), $payload)
            ->assertSessionHasErrors('qualifications.0.start_date');
    });
});
