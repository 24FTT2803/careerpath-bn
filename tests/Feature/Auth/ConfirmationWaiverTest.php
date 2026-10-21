<?php

use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;

/*
 * Accounts an administrator excused from confirming.
 *
 * Verification is asked of students created since the
 * requirement began. That is right for a student who signs
 * themselves up and wrong for one an administrator types in: a
 * demonstration account, or a stand-in for an address that does
 * not exist. Those were asked to confirm a message nothing ever
 * sends, with no way past it.
 *
 * The waiver is deliberately not email_verified_at. That column
 * means the address was confirmed, and writing to it would
 * record something that never happened — the address would then
 * be trusted by anything else that reads it.
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

function waivedStudent(): User
{
    return User::factory()->create([
        'role' => 'student',
        'email_verified_at' => null,
        'created_at' => now(),
        'email_confirmation_waived_at' => now(),
    ]);
}

function anAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

/**
 * A new student as the admin form sends one.
 *
 * The phone is written the way the form's own tests write it,
 * since it is standardised on save rather than taken verbatim.
 *
 * @return array<string, mixed>
 */
function waiverStudentPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Dummy Student',
        'email' => 'dummy.student@student.pb.edu.bn',
        'role' => 'student',
        'student_id' => '24FTT9001',
        'programme' => 'Diploma in ICT (Application Development)',
        'phone' => '712 3456',
        'phone_country' => 'BN',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ], $overrides);
}

/**
 * An existing student, as the edit form resends them.
 *
 * @return array<string, mixed>
 */
function waiverEditPayload(User $student, array $overrides = []): array
{
    return array_merge([
        'name' => $student->name,
        'email' => $student->email,
        'role' => 'student',
        'student_id' => $student->student_id,
        'programme' => $student->programme,
        'phone' => '712 3457',
        'phone_country' => 'BN',
    ], $overrides);
}

describe('getting in', function () {
    test('an excused student is not asked to confirm', function () {
        $this->actingAs(waivedStudent())
            ->get(route('student.dashboard'))
            ->assertSuccessful();
    });

    test('a student nobody excused is still asked', function () {
        /*
         * The point of the default. An exemption that applied to
         * everybody would quietly switch verification off.
         */
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => null,
            'created_at' => now(),
        ]);

        expect($student->mustVerifyEmailAddress())->toBeTrue()
            ->and(waivedStudent()->mustVerifyEmailAddress())->toBeFalse();
    });

    test('being excused is not the same as having confirmed', function () {
        $student = waivedStudent();

        expect($student->hasVerifiedEmail())->toBeFalse()
            ->and($student->email_verified_at)->toBeNull()
            ->and($student->emailConfirmationWaived())->toBeTrue();
    });
});

describe('an administrator granting it', function () {
    beforeEach(function () {
        $this->seed(OrganisationGroupSeeder::class);

        $this->admin = anAdmin();
    });

    test('the create form offers it', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Skip email confirmation')
            ->assertSee('waive_email_confirmation', false);
    });

    test('an account created with it is excused, and says who excused it', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), waiverStudentPayload([
                'waive_email_confirmation' => '1',
            ]))
            ->assertSessionHasNoErrors();

        $student = User::where('email', 'dummy.student@student.pb.edu.bn')->sole();

        expect($student->emailConfirmationWaived())->toBeTrue()
            ->and($student->email_confirmation_waived_by)->toBe($this->admin->id)
            ->and($student->email_verified_at)->toBeNull()
            ->and($student->mustVerifyEmailAddress())->toBeFalse();
    });

    test('an account created without it is asked to confirm as before', function () {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), waiverStudentPayload())
            ->assertSessionHasNoErrors();

        $student = User::where('email', 'dummy.student@student.pb.edu.bn')->sole();

        expect($student->emailConfirmationWaived())->toBeFalse()
            ->and($student->mustVerifyEmailAddress())->toBeTrue();
    });

    test('it is recorded only for students', function () {
        /*
         * Staff are never asked in the first place, so excusing
         * them would record a decision that means nothing.
         */
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'A Lecturer',
                'email' => 'a.lecturer@pb.edu.bn',
                'role' => 'lecturer',
                'phone' => '712 3458',
                'phone_country' => 'BN',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'waive_email_confirmation' => '1',
            ])
            ->assertSessionHasNoErrors();

        expect(User::where('email', 'a.lecturer@pb.edu.bn')->sole()->emailConfirmationWaived())
            ->toBeFalse();
    });
});

describe('an administrator withdrawing it', function () {
    beforeEach(function () {
        $this->seed(OrganisationGroupSeeder::class);

        $this->admin = anAdmin();

        $this->student = User::factory()->create([
            'role' => 'student',
            'email' => 'excused.student@student.pb.edu.bn',
            'email_verified_at' => null,
            'created_at' => now(),
            'student_id' => '24FTT9002',
            'programme' => 'Diploma in ICT (Application Development)',
            'email_confirmation_waived_at' => now(),
            'email_confirmation_waived_by' => $this->admin->id,
        ]);
    });

    test('unticking it asks them to confirm again', function () {
        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student->id), waiverEditPayload($this->student))
            ->assertSessionHasNoErrors();

        $student = $this->student->fresh();

        expect($student->emailConfirmationWaived())->toBeFalse()
            ->and($student->email_confirmation_waived_by)->toBeNull()
            ->and($student->mustVerifyEmailAddress())->toBeTrue();
    });

    test('leaving it ticked keeps the record it already had', function () {
        $when = $this->student->email_confirmation_waived_at;

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->student->id), waiverEditPayload($this->student, [
                'waive_email_confirmation' => '1',
            ]))
            ->assertSessionHasNoErrors();

        expect($this->student->fresh()->email_confirmation_waived_at->timestamp)
            ->toBe($when->timestamp);
    });

    test('the listing says which accounts were excused', function () {
        $this->actingAs($this->admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Confirmation waived');
    });
});
