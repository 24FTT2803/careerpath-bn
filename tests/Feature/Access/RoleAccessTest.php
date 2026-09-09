<?php

use App\Models\User;

test('guest is redirected to login from dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('dashboard shortcut redirects each role correctly', function (string $role, string $route) {
    $user = User::factory()->create([
        'role' => $role,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response->assertRedirect(
        route($route, absolute: false)
    );
})->with([
    'student' => [
        'student',
        'student.dashboard',
    ],
    'lecturer' => [
        'lecturer',
        'lecturer.dashboard',
    ],
    'admin' => [
        'admin',
        'admin.dashboard',
    ],
]);

test('student can access student dashboard', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $this
        ->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertOk();
});

test('lecturer cannot access student dashboard', function () {
    $lecturer = User::factory()->create([
        'role' => 'lecturer',
    ]);

    $this
        ->actingAs($lecturer)
        ->get(route('student.dashboard'))
        ->assertForbidden();
});

test('admin cannot access student dashboard', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('student.dashboard'))
        ->assertForbidden();
});

test('student cannot access lecturer dashboard', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $this
        ->actingAs($student)
        ->get(route('lecturer.dashboard'))
        ->assertForbidden();
});

test('lecturer can access lecturer dashboard', function () {
    $lecturer = User::factory()->create([
        'role' => 'lecturer',
    ]);

    $this
        ->actingAs($lecturer)
        ->get(route('lecturer.dashboard'))
        ->assertOk();
});

test('student and lecturer cannot access admin user management', function (string $role) {
    $user = User::factory()->create([
        'role' => $role,
    ]);

    $this
        ->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
})->with([
    'student',
    'lecturer',
]);

test('admin can access admin user management', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk();
});

test('lecturer and admin can access staff student profile export', function (string $role) {
    $staff = User::factory()->create([
        'role' => $role,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this
        ->actingAs($staff)
        ->get(
            route(
                'student.profile.export.admin',
                $student->id
            )
        );

    expect($response->status())
        ->not->toBe(403);
})->with([
    'lecturer',
    'admin',
]);

test('student cannot use staff student profile export', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $otherStudent = User::factory()->create([
        'role' => 'student',
    ]);

    $this
        ->actingAs($student)
        ->get(
            route(
                'student.profile.export.admin',
                $otherStudent->id
            )
        )
        ->assertForbidden();
});