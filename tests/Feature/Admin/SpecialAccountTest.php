<?php

use App\Models\User;
use App\Services\Business\EntitlementService;
use App\Services\Business\FeatureUsageService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

/*
 * Special accounts: set up by an admin with rules of their own
 * (unlimited AI, Premium, no ads) and roles they may switch
 * between, each role using the account's own data. Every admin
 * can also switch to a student side of their own.
 */
function seedPlansForSpecial(): void
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

function specialStudent(array $rules): User
{
    $student = User::factory()->create(['role' => 'student']);
    $student->forceFill(['special_rules' => $rules])->save();

    return $student->fresh();
}

test('an admin can create a special account; its own role is not listed to switch to', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Demo Lecturer',
            'email' => 'demo.lecturer@pb.edu.bn',
            'role' => 'lecturer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '712 3456',
            'phone_country' => 'BN',
            'special' => [
                'unlimited_ai' => '1',
                'premium' => '0',
                'no_ads' => '1',
                'roles' => ['student', 'lecturer'],
            ],
        ])
        ->assertSessionHasNoErrors();

    $demo = User::where('email', 'demo.lecturer@pb.edu.bn')->sole();

    expect($demo->special_rules)->toBe(['unlimited_ai' => true, 'no_ads' => true, 'roles' => ['student']])
        ->and($demo->switchableRoles())->toBe(['student', 'lecturer'])
        ->and($demo->isSpecial())->toBeTrue();
});

test('an ordinary account saved from the form has no special rules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $lecturer = User::factory()->create(['role' => 'lecturer', 'email' => 'plain@pb.edu.bn']);
    $lecturer->forceFill(['special_rules' => ['premium' => true]])->save();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $lecturer->id), [
            'name' => $lecturer->name,
            'email' => 'plain@pb.edu.bn',
            'role' => 'lecturer',
            'phone' => '712 3457',
            'phone_country' => 'BN',
            'special' => ['unlimited_ai' => '0', 'premium' => '0', 'no_ads' => '0'],
        ])
        ->assertSessionHasNoErrors();

    expect($lecturer->fresh()->special_rules)->toBeNull();
});

test('a special account with Premium has the Premium plan', function () {
    seedPlansForSpecial();
    $student = specialStudent(['premium' => true]);

    $access = app(EntitlementService::class)->accessFor($student);

    expect($access['source'])->toBe('special')
        ->and($access['plan']->code)->toBe('premium');
});

test('a special account with unlimited AI has no limit on any allowance', function () {
    seedPlansForSpecial();
    $student = specialStudent(['unlimited_ai' => true]);

    $status = app(FeatureUsageService::class)->status($student, 'cpbn_bot.token_quota');

    expect($status['allowed'])->toBeTrue()
        ->and($status['mode'])->toBe('unlimited');
});

test('a special account with no ads is never shown any', function () {
    seedPlansForSpecial();
    $student = specialStudent(['no_ads' => true]);

    expect(app(EntitlementService::class)->shouldShowAds($student))->toBeFalse();
});

test('a special account switches to an allowed role and back, using its own data', function () {
    seedPlansForSpecial();
    $student = specialStudent(['roles' => ['lecturer']]);

    $this->actingAs($student)
        ->post(route('role.switch'), ['role' => 'lecturer'])
        ->assertRedirect(route('dashboard'));

    $student->refresh();

    expect($student->role)->toBe('lecturer')
        ->and($student->homeRole())->toBe('student');

    $this->actingAs($student)->get(route('lecturer.dashboard'))->assertOk()->assertSee('Viewing as');

    $this->actingAs($student)
        ->post(route('role.switch'), ['role' => 'student'])
        ->assertRedirect(route('dashboard'));

    expect($student->fresh()->role)->toBe('student')
        ->and($student->fresh()->home_role)->toBeNull();
});

test('a role that is not allowed cannot be switched to', function () {
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)
        ->post(route('role.switch'), ['role' => 'admin'])
        ->assertForbidden();

    expect($student->fresh()->role)->toBe('student');
});

test('every admin can try the student side as themselves, then return', function () {
    seedPlansForSpecial();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Viewing as');

    $this->actingAs($admin)->post(route('role.switch'), ['role' => 'student']);

    $admin->refresh();

    expect($admin->role)->toBe('student')
        ->and($admin->homeRole())->toBe('admin')
        /* Staff trying the student side are never asked to confirm an email. */
        ->and($admin->mustVerifyEmailAddress())->toBeFalse();

    $this->actingAs($admin)->get(route('student.settings'))->assertOk()->assertSee('Viewing as');
    $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();

    $this->actingAs($admin)->post(route('role.switch'), ['role' => 'admin']);

    expect($admin->fresh()->role)->toBe('admin');
});

test('an ordinary student sees no role switcher', function () {
    seedPlansForSpecial();
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)->get(route('student.settings'))->assertOk()->assertDontSee('Viewing as');
});
