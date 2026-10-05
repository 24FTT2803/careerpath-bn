<?php

use App\Models\OrganisationGroup;
use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;

function studentWithId(?string $studentId, array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'student',
        'student_id' => $studentId,
        'email' => fake()->unique()->userName().'@gmail.com',
    ], $attributes));
}

function submitStudentId(User $student, ?string $studentId)
{
    return test()
        ->actingAs($student)
        ->from(route('student.profile.edit'))
        ->put(route('student.profile.update'), [
            'name' => 'Test Student',
            'student_id' => $studentId,
        ]);
}

test('student IDs are stored uppercase without spaces', function () {
    $student = studentWithId(' 24ftt 2803 ');

    expect($student->fresh()->student_id)->toBe('24FTT2803');
});

test('a student ID must follow the Politeknik Brunei format', function (string $invalidId) {
    $student = studentWithId(null);

    submitStudentId($student, $invalidId)->assertSessionHasErrors('student_id');

    expect($student->fresh()->student_id)->toBeNull();
})->with([
    'too short' => '24FTT280',
    'too long' => '24FTT28031',
    'letters where digits go' => 'AAFTT2803',
    'only two letters' => '24FT12803',
    'symbols' => '24-FT-2803',
]);

test('a valid student ID is saved and waits for verification', function () {
    $student = studentWithId(null);

    submitStudentId($student, '24ftt2803')->assertSessionDoesntHaveErrors('student_id');

    $student->refresh();

    expect($student->student_id)->toBe('24FTT2803')
        ->and($student->hasVerifiedStudentId())->toBeFalse();
});

test('one student ID can only belong to one student, whatever the case', function () {
    studentWithId('24FTT2803');
    $second = studentWithId(null);

    submitStudentId($second, '24ftt2803')->assertSessionHasErrors('student_id');

    expect($second->fresh()->student_id)->toBeNull();
});

test('a verified student ID cannot be changed by the student', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = studentWithId('24FTT2803');

    $this->actingAs($admin)->post(route('staff.student-id.verify', $student));

    submitStudentId($student->fresh(), '24FTT9999');

    expect($student->fresh()->student_id)->toBe('24FTT2803')
        ->and($student->fresh()->hasVerifiedStudentId())->toBeTrue();
});

test('changing an unverified ID keeps it pending', function () {
    $student = studentWithId('24FTT2803');

    submitStudentId($student, '24FTT2804');

    expect($student->fresh()->student_id)->toBe('24FTT2804')
        ->and($student->fresh()->hasVerifiedStudentId())->toBeFalse();
});

test('an admin can verify and un-verify any student ID', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = studentWithId('24FTT2803');

    $this->actingAs($admin)
        ->post(route('staff.student-id.verify', $student))
        ->assertRedirect()
        ->assertSessionHas('success');

    $student->refresh();

    expect($student->hasVerifiedStudentId())->toBeTrue()
        ->and($student->student_id_verified_by)->toBe($admin->id);

    $this->actingAs($admin)->delete(route('staff.student-id.revoke', $student));

    expect($student->fresh()->hasVerifiedStudentId())->toBeFalse();
});

test('a lecturer can verify students in their own classes only', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $class = OrganisationGroup::where('name', 'DADT04')->firstOrFail();

    $lecturer = User::factory()->create(['role' => 'lecturer']);
    $lecturer->assignedGroups()->attach($class->id);

    $ownStudent = studentWithId('24FTT0001');
    $ownStudent->groupMemberships()->create(['organisation_group_id' => $class->id]);

    $otherStudent = studentWithId('24FTT0002');

    $this->actingAs($lecturer)
        ->post(route('staff.student-id.verify', $ownStudent))
        ->assertRedirect();

    $this->actingAs($lecturer)
        ->post(route('staff.student-id.verify', $otherStudent))
        ->assertNotFound();

    expect($ownStudent->fresh()->hasVerifiedStudentId())->toBeTrue()
        ->and($otherStudent->fresh()->hasVerifiedStudentId())->toBeFalse();
});

test('students cannot verify student IDs', function () {
    $student = studentWithId('24FTT2803');

    $this->actingAs($student)
        ->post(route('staff.student-id.verify', $student))
        ->assertForbidden();

    expect($student->fresh()->hasVerifiedStudentId())->toBeFalse();
});

test('an ID matching the student\'s confirmed school email is verified automatically', function () {
    $student = studentWithId('24FTT2803', [
        'email' => '24FTT2803@student.pb.edu.bn',
        'email_verified_at' => now(),
    ]);

    expect($student->fresh()->hasVerifiedStudentId())->toBeTrue();
});

test('an unconfirmed school email does not verify the ID', function () {
    $student = studentWithId('24FTT2803', [
        'email' => '24FTT2803@student.pb.edu.bn',
        'email_verified_at' => null,
    ]);

    expect($student->fresh()->hasVerifiedStudentId())->toBeFalse();
});

test('an ID an admin enters on the user form is verified', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $admin = User::factory()->create(['role' => 'admin']);
    $student = studentWithId(null, ['programme' => 'Diploma in ICT (Application Development)']);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $student->id), [
            'name' => 'Test Student',
            'email' => $student->email,
            'role' => 'student',
            'student_id' => '24ftt2803',
            'programme' => 'Diploma in ICT (Application Development)',
        ])
        ->assertSessionHasNoErrors();

    $student->refresh();

    expect($student->student_id)->toBe('24FTT2803')
        ->and($student->hasVerifiedStudentId())->toBeTrue()
        ->and($student->student_id_verified_by)->toBe($admin->id);
});

test('staff pages show the verification status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = studentWithId('24FTT2803');

    $this->actingAs($admin)
        ->get(route('admin.students.show', $student->id))
        ->assertOk()
        ->assertSee('Pending')
        ->assertSee(route('staff.student-id.verify', $student));
});