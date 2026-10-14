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
        ->assertSee('Your student badge', false)
        ->assertSee('Nur Aisyah binti Hassan')
        ->assertSee('nuraisyah@gmail.com')
        ->assertSee('24FTT2803')
        ->assertSee('Pending')
        ->assertSee('Diploma in ICT (Application Development)')
        ->assertSee('+673 712 3456')
        ->assertSee('Member since '.$student->created_at->format('F Y'))
        ->assertSee(route('student.profile.edit'), false);
});

test('the student profile keeps every section on the page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertSee('Profile Completion')
        ->assertSee('Current Studies')
        ->assertSee('Latest CGPA')
        ->assertSee('Personal Details')
        ->assertSee('Skills &amp; Competencies', false)
        ->assertSee('Interests')
        ->assertSee('Projects &amp; Experience', false)
        ->assertSee('Certifications')
        ->assertSee('Career Aspirations')
        ->assertSee('Download PDF')
        ->assertSee('Edit Profile');
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
        ->assertSee('MH')
        ->assertSee('No phone added')
        ->assertSee('Programme not set');
});

test('the edit page explains the current studies fields', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('Current Studies')
        ->assertSee('Your current programme at Politeknik Brunei and your latest CGPA.')
        ->assertSee('Current programme')
        ->assertSee('Latest CGPA (out of 4.00)')
        ->assertSee('placeholder="e.g. 3.25"', false)
        ->assertSee('name="cgpa"', false);
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
        ->assertSee('Current programme')
        ->assertSee('Student ID')
        ->assertSee('3.50')
        ->assertSee('out of 4.00')
        ->assertSee('Date of birth')
        ->assertSee('Nationality')
        ->assertSee('Phone')
        ->assertSee('Not added');
});

test('the badge shows nationality, or a prompt when it is missing', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertSee('Nationality not added');

    $student->profile()->create(['nationality' => 'Brunei Darussalam']);

    $this->actingAs($student->fresh())
        ->get(route('student.profile'))
        ->assertOk()
        ->assertSee('title="Nationality"', false)
        ->assertSee('Brunei Darussalam')
        ->assertDontSee('Nationality not added');
});
