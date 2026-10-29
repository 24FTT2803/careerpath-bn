<?php

use App\Models\User;
use App\Notifications\AuthenticateRegistrationNotification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Support\Facades\Notification;

/*
 * Whether an account has to confirm its address, as opposed to
 * what happens when it does — EmailVerificationTest covers that
 * part.
 *
 * Verification arrived partway through the project, so it is
 * required from a date rather than of everyone. Both sides of
 * that line matter: get it wrong one way and every account that
 * already existed is locked out of itself, the other way and
 * nothing is asked of the new ones.
 */

/*
 * The date the requirement starts, fixed here.
 *
 * Read from the environment it would be whatever the machine
 * running the tests happens to set — and a blank or future
 * AUTH_VERIFICATION_REQUIRED_FROM switches verification off
 * entirely, so every test about being asked to confirm passes
 * by not asking. Which is how three of these were green while
 * the feature they cover did nothing.
 */
beforeEach(function () {
    config(['auth.verification.required_from' => '2026-01-01']);
});

function unverifiedStudent($createdAt = null): User
{
    return User::factory()->create([
        'role' => 'student',
        'email_verified_at' => null,
        'created_at' => $createdAt ?? now(),
    ]);
}

test('signing up sends no confirmation email to an account', function () {
    /*
     * The account is created only once its address has been
     * authenticated, so it starts confirmed and the old
     * confirmation email has nothing left to confirm.
     * RegistrationTest covers the email that is sent instead.
     */
    Notification::fake();

    $this->seed(OrganisationGroupSeeder::class);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test.user@pb.edu.bn',
        'programme' => 'Diploma in ICT (Application Development)',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => 'on',
    ]);

    Notification::assertSentOnDemand(
        AuthenticateRegistrationNotification::class,
        function (AuthenticateRegistrationNotification $notification) {
            $this->post($notification->linkTo('register.authenticate'));

            return true;
        }
    );

    $user = User::where('email', 'test.user@pb.edu.bn')->sole();

    expect($user->hasVerifiedEmail())->toBeTrue();

    Notification::assertNotSentTo($user, VerifyEmailNotification::class);
});

test(
    'a student who signed up since is asked to confirm',
    function () {
        $this->actingAs(unverifiedStudent())
            ->get(route('student.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }
);

test(
    'an account from before the requirement is left alone',
    function () {
        /*
         * Nobody who signed up earlier ever had the chance to
         * confirm, so requiring it now would shut them out of
         * an account they already had.
         */
        $student = unverifiedStudent(
            Carbon::parse(
                config('auth.verification.required_from')
            )->subDay()
        );

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertSuccessful();
    }
);

test('a confirmed student carries on as normal', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertSuccessful();
});

test('staff are never asked to confirm', function () {
    /*
     * Their accounts are made by an administrator rather than
     * signed up for, so no confirmation is ever sent and asking
     * for one would be a door with no key.
     */
    foreach (['admin', 'lecturer'] as $role) {
        $staff = User::factory()->create([
            'role' => $role,
            'email_verified_at' => null,
        ]);

        expect($staff->mustVerifyEmailAddress())->toBeFalse();
    }
});

test('the requirement can be switched off entirely', function () {
    config(['auth.verification.required_from' => null]);

    $this->actingAs(unverifiedStudent())
        ->get(route('student.dashboard'))
        ->assertSuccessful();
});

test(
    'an unconfirmed address can be confirmed from settings',
    function () {
        Notification::fake();

        /*
         * An older account is not made to confirm, but nothing
         * should stop one choosing to.
         */
        $student = unverifiedStudent(
            Carbon::parse(
                config('auth.verification.required_from')
            )->subDay()
        );

        $this->actingAs($student)
            ->get(route('student.settings'))
            ->assertSuccessful()
            ->assertSee('Send confirmation link', false);

        $this->actingAs($student)
            ->post(route('verification.send'));

        Notification::assertSentTo(
            $student,
            VerifyEmailNotification::class
        );
    }
);
