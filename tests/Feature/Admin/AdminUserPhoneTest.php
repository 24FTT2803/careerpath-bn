<?php

use App\Models\StudentProfile;
use App\Models\User;

function adminForPhone(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function lecturerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Dr. Hajah Rosnah',
        'email' => 'rosnah@pb.edu.bn',
        'role' => 'lecturer',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '712 3456',
        'phone_country' => 'BN',
    ], $overrides);
}

test('an admin-created user gets the phone in the standard format', function () {
    $this->actingAs(adminForPhone())
        ->post(route('admin.users.store'), lecturerPayload())
        ->assertSessionHasNoErrors();

    $user = User::where('email', 'rosnah@pb.edu.bn')->firstOrFail();

    expect($user->phone)->toBe('+6737123456')
        ->and(StudentProfile::where('user_id', $user->id)->value('phone'))->toBe('+6737123456');
});

test('an admin cannot give a new user a phone number already in use', function () {
    User::factory()->create(['role' => 'student', 'phone' => '+6737123456']);

    $this->actingAs(adminForPhone())
        ->post(route('admin.users.store'), lecturerPayload(['phone' => '+673 712-3456']))
        ->assertSessionHasErrors([
            'phone' => 'This phone number is already registered to another account.',
        ]);

    expect(User::where('email', 'rosnah@pb.edu.bn')->exists())->toBeFalse();
});

test('an admin cannot move an existing user onto a number already in use', function () {
    User::factory()->create(['role' => 'student', 'phone' => '+6737123456']);
    $lecturer = User::factory()->create(['role' => 'lecturer', 'email' => 'lect.edit@pb.edu.bn', 'phone' => null]);

    $this->actingAs(adminForPhone())
        ->put(route('admin.users.update', $lecturer->id), [
            'name' => $lecturer->name,
            'email' => $lecturer->email,
            'role' => 'lecturer',
            'phone' => '7123456',
            'phone_country' => 'BN',
        ])
        ->assertSessionHasErrors([
            'phone' => 'This phone number is already registered to another account.',
        ]);

    expect($lecturer->fresh()->phone)->toBeNull();
});

test('editing a user and keeping their own number is fine', function () {
    $lecturer = User::factory()->create(['role' => 'lecturer', 'email' => 'lect.keep@pb.edu.bn', 'phone' => '+6737123456']);

    $this->actingAs(adminForPhone())
        ->put(route('admin.users.update', $lecturer->id), [
            'name' => 'Updated Name',
            'email' => $lecturer->email,
            'role' => 'lecturer',
            'phone' => '+6737123456',
            'phone_country' => 'BN',
        ])
        ->assertSessionHasNoErrors();

    expect($lecturer->fresh()->phone)->toBe('+6737123456')
        ->and($lecturer->fresh()->name)->toBe('Updated Name');
});

test('the phone can be left empty', function () {
    $this->actingAs(adminForPhone())
        ->post(route('admin.users.store'), lecturerPayload(['phone' => '', 'phone_country' => 'BN']))
        ->assertSessionHasNoErrors();

    expect(User::where('email', 'rosnah@pb.edu.bn')->value('phone'))->toBeNull();
});

test('an invalid number for the country is refused', function () {
    $this->actingAs(adminForPhone())
        ->post(route('admin.users.store'), lecturerPayload(['phone' => '12']))
        ->assertSessionHasErrors('phone');
});

test('the admin user forms show the country picker fields', function () {
    $admin = adminForPhone();
    $lecturer = User::factory()->create(['role' => 'lecturer']);

    $this->actingAs($admin)->get(route('admin.users.create'))
        ->assertOk()->assertSee('name="phone_country"', false);

    $this->actingAs($admin)->get(route('admin.users.edit', $lecturer->id))
        ->assertOk()->assertSee('name="phone_country"', false);
});
