<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the student and lecturer badges skip the page fade so they do not flash', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertShows('aria-label="Your student badge" data-motion-skip');

    $lecturer = User::factory()->create(['role' => 'lecturer']);

    $this->actingAs($lecturer)
        ->get(route('lecturer.settings'))
        ->assertOk()
        ->assertShows('aria-label="Your lecturer badge" data-motion-skip');
});
