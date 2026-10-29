<?php

use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\AuthenticateRegistrationNotification;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Support\Facades\Notification;

/*
 * Signing up.
 *
 * Only Politeknik Brunei addresses are accepted, and no account
 * exists until the link emailed to the address is used. The
 * email's other link, "This isn't me", discards the sign-up.
 */

function signUpPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => '24FTT2803@student.pb.edu.bn',
        'programme' => 'Diploma in ICT (Application Development)',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => 'on',
    ], $overrides);
}

/**
 * The given link from the sign-up email sent.
 */
function linkFromSignUpEmail(string $routeName = 'register.authenticate'): string
{
    $link = null;

    Notification::assertSentOnDemand(
        AuthenticateRegistrationNotification::class,
        function (AuthenticateRegistrationNotification $notification) use (&$link, $routeName) {
            $link = $notification->linkTo($routeName);

            return true;
        }
    );

    return $link;
}

/**
 * Sign up, and return the given link from the email sent.
 */
function signUpAndGetLink(string $routeName = 'register.authenticate', array $overrides = []): string
{
    Notification::fake();

    test()->seed(OrganisationGroupSeeder::class);

    test()->post('/register', signUpPayload($overrides))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('register.sent'));

    return linkFromSignUpEmail($routeName);
}

test('registration screen can be rendered', function () {
    $this->get('/register')->assertStatus(200);
});

test('signing up creates no account until the email is authenticated', function () {
    signUpAndGetLink();

    $this->assertGuest();

    expect(User::where('email', '24ftt2803@student.pb.edu.bn')->exists())->toBeFalse()
        ->and(PendingRegistration::where('email', '24ftt2803@student.pb.edu.bn')->exists())->toBeTrue();
});

test('the email is sent to the address that signed up', function () {
    signUpAndGetLink();

    Notification::assertSentOnDemand(
        AuthenticateRegistrationNotification::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === '24ftt2803@student.pb.edu.bn'
    );
});

test('the email offers Authenticate and This isn\'t me', function () {
    signUpAndGetLink();

    $html = null;

    Notification::assertSentOnDemand(
        AuthenticateRegistrationNotification::class,
        function (AuthenticateRegistrationNotification $notification, $channels, $notifiable) use (&$html) {
            $html = (string) $notification->toMail($notifiable)->render();

            return true;
        }
    );

    expect($html)
        ->toContain('Authenticate')
        ->toContain("This isn't me")
        ->toContain('/authenticate?')
        ->toContain('/not-me?');
});

test('the check your email page names the address', function () {
    signUpAndGetLink();

    $this->get(route('register.sent'))
        ->assertOk()
        ->assertSee('Check your email')
        ->assertSee('24ftt2803@student.pb.edu.bn');
});

test('opening the authenticate link only asks, it does not create the account', function () {
    $link = signUpAndGetLink();

    $this->get($link)
        ->assertOk()
        ->assertSee('Authenticate your account');

    expect(User::count())->toBe(0);
});

test('authenticating creates a confirmed account and signs the student in', function () {
    $link = signUpAndGetLink();

    $this->post($link)->assertRedirect(route('student.dashboard'));

    $user = User::where('email', '24ftt2803@student.pb.edu.bn')->sole();

    $this->assertAuthenticatedAs($user);

    expect($user->role)->toBe('student')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(PendingRegistration::count())->toBe(0);
});

test('a student.pb.edu.bn mailbox becomes the verified Student ID', function () {
    $link = signUpAndGetLink();

    $this->post($link);

    $user = User::where('email', '24ftt2803@student.pb.edu.bn')->sole();

    expect($user->student_id)->toBe('24FTT2803')
        ->and($user->hasVerifiedStudentId())->toBeTrue();
});

test('a pb.edu.bn address signs up with no Student ID', function () {
    $link = signUpAndGetLink(overrides: ['email' => 'test.user@pb.edu.bn']);

    $this->post($link);

    $user = User::where('email', 'test.user@pb.edu.bn')->sole();

    expect($user->student_id)->toBeNull();
});

test('an authenticate link cannot be used twice', function () {
    $link = signUpAndGetLink();

    $this->post($link);
    auth()->logout();

    $this->post($link)
        ->assertOk()
        ->assertSee('This link no longer works');

    expect(User::count())->toBe(1);
});

test('this isn\'t me discards the sign-up', function () {
    $link = signUpAndGetLink('register.decline');

    $this->get($link)
        ->assertOk()
        ->assertSee('Not you?');

    expect(PendingRegistration::count())->toBe(1);

    $this->post($link)
        ->assertOk()
        ->assertSee('Sign-up discarded');

    expect(PendingRegistration::count())->toBe(0)
        ->and(User::count())->toBe(0);
});

test('after this isn\'t me the authenticate link no longer works', function () {
    $decline = signUpAndGetLink('register.decline');
    $authenticate = linkFromSignUpEmail('register.authenticate');

    $this->post($decline);

    $this->post($authenticate)->assertSee('This link no longer works');

    expect(User::count())->toBe(0);
});

test('an expired link does not create the account', function () {
    $link = signUpAndGetLink();

    $this->travel(61)->minutes();

    $this->post($link)->assertSee('This link no longer works');

    expect(User::count())->toBe(0);
});

test('a tampered link does not create the account', function () {
    $link = signUpAndGetLink();

    $this->post(str_replace('signature=', 'signature=0', $link))
        ->assertSee('This link no longer works');

    expect(User::count())->toBe(0);
});

test('signing up again replaces the earlier email\'s links', function () {
    $first = signUpAndGetLink();

    $this->post('/register', signUpPayload(['name' => 'Second Try']));

    $this->post($first)->assertSee('This link no longer works');

    expect(PendingRegistration::sole()->name)->toBe('Second Try')
        ->and(User::count())->toBe(0);
});

test('the link can be sent again', function () {
    signUpAndGetLink();

    $this->post(route('register.resend'))
        ->assertRedirect(route('register.sent'))
        ->assertSessionHas('status', 'registration-link-sent');

    Notification::assertSentOnDemandTimes(AuthenticateRegistrationNotification::class, 2);
});

test('registration refuses an address from outside', function (string $email) {
    $this->seed(OrganisationGroupSeeder::class);

    $this->post('/register', signUpPayload(['email' => $email]))
        ->assertSessionHasErrors('email');

    $this->assertGuest();

    expect(PendingRegistration::count())->toBe(0);
})->with([
    'example.com' => 'test@example.com',
    'gmail.com' => 'test@gmail.com',
    'a look-alike domain' => 'test@pb.edu.bn.example.com',
]);

test('a student.pb.edu.bn mailbox must be a Student ID', function () {
    $this->seed(OrganisationGroupSeeder::class);

    $this->post('/register', signUpPayload(['email' => 'test.user@student.pb.edu.bn']))
        ->assertSessionHasErrors(['email' => 'A student.pb.edu.bn address must be your Student ID, like 24FTT2803@student.pb.edu.bn.']);
});

test('a Student ID that already has an account cannot sign up again', function () {
    $this->seed(OrganisationGroupSeeder::class);

    User::factory()->create([
        'role' => 'student',
        'email' => 'someone@pb.edu.bn',
        'student_id' => '24FTT2803',
    ]);

    $this->post('/register', signUpPayload())
        ->assertSessionHasErrors('email');
});

test('an address that already has an account cannot sign up again', function () {
    $this->seed(OrganisationGroupSeeder::class);

    User::factory()->create(['email' => '24ftt2803@student.pb.edu.bn']);

    $this->post('/register', signUpPayload())
        ->assertSessionHasErrors('email');

    expect(PendingRegistration::count())->toBe(0);
});
