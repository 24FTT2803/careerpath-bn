<?php

use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Nur Aisyah binti Hassan',
        'email' => 'nuraisyah@gmail.com',
        'programme' => 'Diploma in ICT (Application Development)',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => '1',
    ], $overrides);
}

test('students sign up with one full name', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $this->post(route('register'), registrationPayload())
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'nuraisyah@gmail.com')->firstOrFail();

    expect($user->name)->toBe('Nur Aisyah binti Hassan');
    $this->assertAuthenticatedAs($user);
});

test('sign up requires a full name', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $this->post(route('register'), registrationPayload(['name' => '']))
        ->assertSessionHasErrors(['name' => 'Please enter your full name.']);

    expect(User::where('email', 'nuraisyah@gmail.com')->exists())->toBeFalse();
});

test('extra spaces in a full name are tidied', function () {
    $user = User::factory()->create(['name' => '  Pg  Muhammad   Hafiz bin Pg Abdullah ']);

    expect($user->fresh()->name)->toBe('Pg Muhammad Hafiz bin Pg Abdullah');
});

test('students can change their full name on the profile page', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'name' => 'Old Name',
        'student_id' => '24FTT2803',
    ]);

    $this->actingAs($student)
        ->from(route('student.profile.edit'))
        ->put(route('student.profile.update'), [
            'name' => 'Muhammad Hafiz bin Abdullah',
            'student_id' => '24FTT2803',
        ])
        ->assertSessionHasNoErrors();

    expect($student->fresh()->name)->toBe('Muhammad Hafiz bin Abdullah');
});

test('the profile page shows a single full name field', function () {
    $student = User::factory()->create(['role' => 'student', 'name' => 'Nur Aisyah binti Hassan']);

    $this->actingAs($student)
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('Full Name (as on IC)')
        ->assertSee('value="Nur Aisyah binti Hassan"', false)
        ->assertDontSee('name="first_name"', false)
        ->assertDontSee('name="last_name"', false);
});

test('the dashboard greets the student by full name', function () {
    $student = User::factory()->create(['role' => 'student', 'name' => 'Nur Aisyah binti Hassan']);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('Nur Aisyah binti Hassan');
});