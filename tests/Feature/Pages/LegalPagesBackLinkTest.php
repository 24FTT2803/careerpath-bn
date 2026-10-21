<?php

use App\Models\User;

test('legal pages go back to registration when opened from sign up', function (string $page) {
    $this->from(url('/register'))
        ->get(url($page))
        ->assertOk()
        ->assertSee('Back to Registration')
        ->assertSee('href="'.url('/register').'"', false);
})->with(['/privacy', '/terms']);

test('legal pages go back to the home page when opened from the footer there', function (string $page) {
    $this->from(url('/'))
        ->get(url($page))
        ->assertOk()
        ->assertSee('Back to Home')
        ->assertDontSee('Back to Registration');
})->with(['/privacy', '/terms']);

test('legal pages go back to the page a student came from', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->from(route('student.dashboard'))
        ->get(route('privacy'))
        ->assertOk()
        ->assertSee('Back to Dashboard')
        ->assertSee('href="'.route('student.dashboard').'"', false);
});

test('legal pages fall back to the dashboard or home when opened directly', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('Back to Home');

    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('terms'))
        ->assertOk()
        ->assertSee('Back to Dashboard')
        ->assertSee('href="'.route('dashboard').'"', false);
});

test('legal pages ignore links from other websites', function () {
    $this->from('https://example.com/somewhere')
        ->get(route('privacy'))
        ->assertOk()
        ->assertSee('Back to Home')
        ->assertDontSee('example.com/somewhere');
});

test('legal pages list both contact emails', function (string $page) {
    $this->get(url($page))
        ->assertOk()
        ->assertSee('contact@pb.edu.bn')
        ->assertSee('careerpathbn@gmail.com');
})->with(['/privacy', '/terms']);
