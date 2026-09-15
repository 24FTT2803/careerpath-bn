<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a milestone with a clean title saves successfully', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $this->actingAs($student)
        ->post(route('student.milestones.store'), [
            'title' => 'Complete Laravel tutorial',
            'category' => 'skill',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('student.milestones'));

    expect(
        $student->milestones()->where(
            'title',
            'Complete Laravel tutorial'
        )->exists()
    )->toBeTrue();
});

test('a milestone with profanity in the title is rejected', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $this->actingAs($student)
        ->post(route('student.milestones.store'), [
            'title' => 'fuck this module',
            'category' => 'academic',
        ])
        ->assertSessionHasErrors('title');

    expect($student->milestones()->count())->toBe(0);
});

test('a milestone with a legitimate name-like word is allowed', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $this->actingAs($student)
        ->post(route('student.milestones.store'), [
            'title' => 'Read Dickinson poetry',
            'category' => 'personal',
        ])
        ->assertSessionHasNoErrors();

    expect($student->milestones()->count())->toBe(1);
});

test('a milestone description is not filtered', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    /*
     * Descriptions are narrative text. A student writing about
     * offensive security must not be blocked, so the rule is
     * deliberately not applied to this field.
     */
    $this->actingAs($student)
        ->post(route('student.milestones.store'), [
            'title' => 'Study cybersecurity',
            'category' => 'skill',
            'description' => 'Learn offensive security and penetration testing techniques.',
        ])
        ->assertSessionHasNoErrors();

    expect($student->milestones()->count())->toBe(1);
});