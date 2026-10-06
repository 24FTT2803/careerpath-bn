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
        ->assertSee('Academic Information')
        ->assertSee('CGPA')
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
