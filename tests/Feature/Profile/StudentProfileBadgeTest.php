<?php

use App\Models\User;

test('the student profile shows a badge with their personal details', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Nur Aisyah binti Hassan',
        'email' => 'nuraisyah@gmail.com',
        'student_id' => '24FTT2803',
        'programme' => 'Diploma in ICT (Application Development)',
    ]);
    $student->profile()->create(['phone' => '+6737123456']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('Your student badge')
        ->assertShows('Nur Aisyah binti Hassan')
        ->assertShows('nuraisyah@gmail.com')
        ->assertShows('24FTT2803')
        ->assertShows('Pending')
        ->assertShows('Diploma in ICT (Application Development)')
        ->assertShows('+673 712 3456')
        ->assertSee('Member since '.$student->created_at->format('F Y'))
        ->assertSee(route('student.profile.edit'), false);
});

test('the student profile keeps every section on the page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('Profile Completion')
        ->assertShows('Current Studies')
        ->assertShows('Latest CGPA')
        ->assertShows('Personal Details')
        ->assertShows('Skills &amp; Competencies')
        ->assertShows('Interests')
        ->assertShows('Projects &amp; Experience')
        ->assertShows('Certifications')
        ->assertShows('Career Aspirations')
        ->assertShows('Download PDF')
        ->assertShows('Edit Profile');
});

test('the badge shows initials and a hint when details are missing', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Muhammad Hafiz',
        'programme' => null,
    ]);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('MH')
        ->assertShows('No phone added')
        ->assertShows('Programme not set');
});

test('the edit page asks for the programme among the qualifications', function () {
    /*
     * This asked for a Current Studies section, which the form no
     * longer has. It held the programme a second time beside the
     * qualification already recording it, and asked a
     * post-graduate — who has no current studies — to fill in a
     * section built around having some.
     *
     * The programme is now the first qualification, pre-filled
     * and not removable, and the CGPA sits in it.
     */
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertShows('Qualifications')
        ->assertShows('Your programme')
        ->assertShows('Politeknik Brunei')
        ->assertShows('placeholder="e.g. 3.25"')
        ->assertShows('name="cgpa"')
        ->assertHides('Current Studies');
});

test('my profile shows current studies and personal details, with prompts for missing ones', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'student_id' => null,
        'cgpa' => 3.5,
    ]);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('Current programme')
        ->assertShows('Student ID')
        ->assertShows('3.50')
        ->assertShows('out of 4.00')
        ->assertShows('Date of birth')
        ->assertShows('Nationality')
        ->assertShows('Phone')
        ->assertShows('Not added');
});

test('the badge shows nationality, or a prompt when it is missing', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('Nationality not added');

    $student->profile()->create(['nationality' => 'Brunei Darussalam']);

    $this->actingAs($student->fresh())
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('title="Nationality"')
        ->assertShows('Brunei Darussalam')
        ->assertHides('Nationality not added');
});
