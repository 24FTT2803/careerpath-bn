<?php

use App\Models\User;

/*
 * These began as Breeze's profile tests, written against a single
 * form that edited a name and an email address at /profile. The
 * student profile replaced it: the routes moved under the student
 * prefix, the form carries programme, competencies and interests,
 * and the email address is not editable there at all.
 */

function student(): User
{
    return User::factory()->create(['role' => 'student']);
}

test('profile page is displayed', function () {
    $response = $this
        ->actingAs(student())
        ->get(route('student.profile'));

    $response->assertOk();
});

test(
    'an update missing required details is refused',
    function () {
        $student = student();

        $response = $this
            ->actingAs($student)
            ->from(route('student.profile'))
            ->put(route('student.profile.update'), [
                'name' => 'Test User',
            ]);

        /*
         * The profile carries far more than a name now, so a
         * partial submission should not quietly save half of it.
         */
        $response->assertSessionHasErrors();
    }
);

test(
    'the email address cannot be changed from the profile',
    function () {
        $student = student();
        $original = $student->email;

        $this
            ->actingAs($student)
            ->put(route('student.profile.update'), [
                'name' => 'Test User',
                'email' => 'somewhere.else@student.pb.edu.bn',
            ]);

        /*
         * Breeze let the profile form change an email and then
         * reset the verification. Nothing in the student
         * controllers accepts an email any more, and this is
         * what keeps it that way.
         */
        expect($student->fresh()->email)->toBe($original)
            ->and($student->fresh()->email_verified_at)
            ->not->toBeNull();
    }
);

test('user can delete their account', function () {
    $student = student();

    $response = $this
        ->actingAs($student)
        ->delete(route('student.profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($student->fresh());
});

test(
    'correct password must be provided to delete account',
    function () {
        $student = student();

        $response = $this
            ->actingAs($student)
            ->from(route('student.profile'))
            ->delete(route('student.profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        /*
         * Deleting takes the account and everything attached to
         * it, so being signed in is not enough on its own.
         */
        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('student.profile'));

        $this->assertNotNull($student->fresh());
    }
);
