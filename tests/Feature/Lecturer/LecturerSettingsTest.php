<?php

use App\Models\OrganisationGroup;
use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function lecturerForSettings(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'lecturer',
        'name' => 'Dr. Siti Aminah',
        'password' => Hash::make('secret-pass'),
    ], $attributes));
}

test('a lecturer can open their settings page', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)
        ->get(route('lecturer.settings'))
        ->assertOk()
        ->assertSee('Profile')
        ->assertSee('Delete account')
        ->assertSee('value="Dr. Siti Aminah"', false);
});

test('the lecturer sidebar links to settings', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)
        ->get(route('lecturer.dashboard'))
        ->assertOk()
        ->assertSee(route('lecturer.settings'));
});

test('students and admins cannot use lecturer settings', function (string $role, int $status) {
    $user = User::factory()->create(['role' => $role]);

    $this->actingAs($user)
        ->get(route('lecturer.settings'))
        ->assertStatus($status);
})->with([
    'student' => ['student', 403],
    'admin' => ['admin', 403],
]);

test('a lecturer can update their name and phone', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)
        ->put(route('lecturer.settings.profile'), [
            'name' => '  Dr.  Siti Aminah binti Yusof ',
            'phone' => '712 3456',
            'phone_country' => 'BN',
        ])
        ->assertRedirect(route('lecturer.settings'))
        ->assertSessionHasNoErrors();

    $lecturer->refresh();

    expect($lecturer->name)->toBe('Dr. Siti Aminah binti Yusof')
        ->and($lecturer->phone)->toBe('+6737123456');
});

test('a phone number already used by another account is refused', function () {
    User::factory()->create(['role' => 'student', 'phone' => '+6737123456']);
    $lecturer = lecturerForSettings(['phone' => null]);

    $this->actingAs($lecturer)
        ->put(route('lecturer.settings.profile'), [
            'name' => $lecturer->name,
            'phone' => '+673 712-3456',
            'phone_country' => 'BN',
        ])
        ->assertSessionHasErrors([
            'phone' => 'This phone number is already registered to another account.',
        ]);

    expect($lecturer->fresh()->phone)->toBeNull();
});

test('a lecturer can keep their own phone number when saving', function () {
    $lecturer = lecturerForSettings(['phone' => '+6737123456']);

    $this->actingAs($lecturer)
        ->put(route('lecturer.settings.profile'), [
            'name' => $lecturer->name,
            'phone' => '+6737123456',
            'phone_country' => 'BN',
        ])
        ->assertSessionHasNoErrors();

    expect($lecturer->fresh()->phone)->toBe('+6737123456');
});

test('an invalid phone number for the country is refused', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)
        ->put(route('lecturer.settings.profile'), [
            'name' => $lecturer->name,
            'phone' => '12',
            'phone_country' => 'BN',
        ])
        ->assertSessionHasErrors('phone');
});

test('a lecturer must keep a name', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)
        ->put(route('lecturer.settings.profile'), ['name' => ''])
        ->assertSessionHasErrors('name');

    expect($lecturer->fresh()->name)->toBe('Dr. Siti Aminah');
});

test('a lecturer can upload, replace and remove a profile picture', function () {
    Storage::fake('public');
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)->put(route('lecturer.settings.profile'), [
        'name' => $lecturer->name,
        'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
    ])->assertSessionHasNoErrors();

    $first = $lecturer->fresh()->avatar;
    expect($first)->not->toBeNull();
    Storage::disk('public')->assertExists($first);

    $this->actingAs($lecturer)->put(route('lecturer.settings.profile'), [
        'name' => $lecturer->name,
        'avatar' => UploadedFile::fake()->image('new.png', 200, 200),
    ]);

    $second = $lecturer->fresh()->avatar;
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    $this->actingAs($lecturer)->put(route('lecturer.settings.profile'), [
        'name' => $lecturer->name,
        'remove_avatar' => '1',
    ]);

    expect($lecturer->fresh()->avatar)->toBeNull();
    Storage::disk('public')->assertMissing($second);
});

test('only images can be used as a profile picture', function () {
    Storage::fake('public');
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)->put(route('lecturer.settings.profile'), [
        'name' => $lecturer->name,
        'avatar' => UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf'),
    ])->assertSessionHasErrors('avatar');

    expect($lecturer->fresh()->avatar)->toBeNull();
});

test('a lecturer can change their password with the current one', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)->put(route('lecturer.settings.password'), [
        'current_password' => 'secret-pass',
        'password' => 'new-secret-pass',
        'password_confirmation' => 'new-secret-pass',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('new-secret-pass', $lecturer->fresh()->password))->toBeTrue();
});

test('a wrong current password does not change the password', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)->put(route('lecturer.settings.password'), [
        'current_password' => 'wrong',
        'password' => 'new-secret-pass',
        'password_confirmation' => 'new-secret-pass',
    ])->assertSessionHasErrors('current_password');

    expect(Hash::check('secret-pass', $lecturer->fresh()->password))->toBeTrue();
});

test('deleting the account needs the correct password', function () {
    $lecturer = lecturerForSettings();

    $this->actingAs($lecturer)
        ->delete(route('lecturer.settings.destroy'), ['delete_password' => 'wrong'])
        ->assertSessionHasErrorsIn('deleteAccount', 'delete_password');

    expect(User::find($lecturer->id))->not->toBeNull();
    $this->assertAuthenticatedAs($lecturer);
});

test('a lecturer can delete their account without affecting students', function () {
    $this->seed(OrganisationGroupSeeder::class);
    Storage::fake('public');

    $class = OrganisationGroup::where('name', 'DADT04')->firstOrFail();
    $lecturer = lecturerForSettings(['avatar' => UploadedFile::fake()->image('me.jpg')->store('avatars', 'public')]);
    $lecturer->assignedGroups()->attach($class->id);

    $student = User::factory()->create(['role' => 'student', 'student_id' => '24FTT0099']);
    $student->groupMemberships()->create(['organisation_group_id' => $class->id]);
    $student->forceFill([
        'student_id_verified_at' => now(),
        'student_id_verified_by' => $lecturer->id,
    ])->save();

    $avatar = $lecturer->avatar;

    $this->actingAs($lecturer)
        ->delete(route('lecturer.settings.destroy'), ['delete_password' => 'secret-pass'])
        ->assertRedirect('/');

    $this->assertGuest();
    expect(User::find($lecturer->id))->toBeNull();
    $this->assertDatabaseMissing('lecturer_assignments', ['user_id' => $lecturer->id]);
    Storage::disk('public')->assertMissing($avatar);

    $student->refresh();
    expect($student->exists)->toBeTrue()
        ->and($student->hasVerifiedStudentId())->toBeTrue()
        ->and($student->student_id_verified_by)->toBeNull()
        ->and($student->groupMemberships()->count())->toBe(1);
});
