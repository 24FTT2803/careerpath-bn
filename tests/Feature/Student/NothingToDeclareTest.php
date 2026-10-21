<?php

use App\Models\User;

/*
 * Having no projects and no certifications.
 *
 * Both used to count towards a complete profile, so a student
 * with neither could never finish one — and plenty have neither.
 * A first-year has built no portfolio and sat no certification.
 *
 * The first answer to that was a box to tick saying you had
 * none, which made a student do ceremony to say the ordinary
 * thing. The simpler reading is that the two sections are
 * optional: having none is the default and needs no declaring,
 * and adding one is the statement.
 */

function declaringStudent(): User
{
    $student = User::factory()->create([
        'role' => 'student',
        'student_id' => '24FTT2803',
        'programme' => 'Diploma in Information Technology',
        'cgpa' => 3.4,
    ]);

    $student->profile()->create(['nationality' => 'Bruneian']);

    $student->aspirations()->create(['vision_statement' => 'To work in network security.']);

    $student->interests()->create([
        'interest_name' => 'Networking',
        'category' => 'career',
    ]);

    $student->competencies()->create([
        'skill_name' => 'Networking',
        'category' => 'technical',
        'proficiency_level' => 'intermediate',
    ]);

    return $student;
}

test('a student with neither has a complete profile', function () {
    $student = declaringStudent();

    expect($student->projects()->count())->toBe(0)
        ->and($student->certifications()->count())->toBe(0)
        ->and((int) $student->profile_completion)->toBe(100);
});

test('adding a project does not change what complete means', function () {
    /*
     * It is still worth having: it counts towards readiness and
     * it reaches the adviser. It is only being finished that it
     * does not gate.
     */
    $student = declaringStudent();

    $student->projects()->create([
        'title' => 'Campus network redesign',
        'description' => 'Resegmented the lab network.',
    ]);

    expect((int) $student->fresh()->profile_completion)->toBe(100);
});

test('the sections a profile does need are still counted', function () {
    $student = declaringStudent();

    $student->interests()->delete();

    expect((int) $student->fresh()->profile_completion)->toBeLessThan(100);
});

describe('the form', function () {
    test('it offers to add one, and says the section is optional', function () {
        $this->actingAs(declaringStudent())
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertSee('Add Project')
            ->assertSee('Add Certification')
            ->assertSee('your profile still counts as complete', false);
    });

    test('no declaration is asked for or stored', function () {
        /*
         * The checkbox and the two columns behind it are gone, so
         * a save that still mentions them changes nothing rather
         * than failing on a column that is not there.
         */
        $student = declaringStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), [
                'name' => $student->name,
                'student_id' => $student->student_id,
                'declared_no_projects' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect($student->fresh()->profile->getAttributes())
            ->not->toHaveKey('declared_no_projects');
    });

    test('a project sent with the form is saved', function () {
        $student = declaringStudent();

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), [
                'name' => $student->name,
                'student_id' => $student->student_id,
                'projects' => [
                    [
                        'title' => 'Campus network redesign',
                        'description' => 'Resegmented the lab network.',
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        expect($student->projects()->count())->toBe(1)
            ->and($student->projects()->value('title'))->toBe('Campus network redesign');
    });
});
