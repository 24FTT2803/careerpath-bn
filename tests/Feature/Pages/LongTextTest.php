<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student and staff pages load the long-text wrapping rules', function () {
    $student = User::factory()->create(['role' => 'student']);
    $lecturer = User::factory()->create(['role' => 'lecturer']);

    $this->actingAs($student)
        ->get(route('student.profile'))
        ->assertOk()
        ->assertSee('body .info-row .value', false);

    $this->actingAs($lecturer)
        ->get(route('lecturer.students.index'))
        ->assertOk()
        ->assertSee('body .lecturer-student-list .admin-table .cell-title', false)
        ->assertSee('class="lecturer-student-list"', false);
});
