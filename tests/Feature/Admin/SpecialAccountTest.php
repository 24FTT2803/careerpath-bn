<?php

use App\Models\FeatureDefinition;
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

function featureId(string $key): int
{
    return FeatureDefinition::where('key', $key)->sole()->id;
}

function specialStudent(array $rules): User
{
    $student = User::factory()->create(['role' => 'student']);
    $student->forceFill(['special_rules' => $rules])->save();

    return $student->fresh();
}

test('an admin can create a special account with its own plan, feature values and roles', function () {
    seedPlansForSpecial();
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
            'special_enabled' => '1',
            'special' => [
                'plan' => 'premium',
                /* By id: the keys hold dots, which a form name would split. */
                'features' => [
                    featureId('cpbn_bot.token_quota') => ['custom' => '1', 'mode' => 'recurring', 'amount' => '5000', 'period_value' => '1', 'period_unit' => 'day'],
                    featureId('ads.available') => ['custom' => '1', 'value' => '0'],
                    featureId('career_adviser.usage_quota') => ['custom' => '0', 'mode' => 'unlimited'],
                ],
                'roles' => ['student', 'lecturer'],
            ],
        ])
        ->assertSessionHasNoErrors();

    $demo = User::where('email', 'demo.lecturer@pb.edu.bn')->sole();

    expect($demo->special_rules)->toEqual([
        'plan' => 'premium',
        'features' => [
            'cpbn_bot.token_quota' => ['mode' => 'recurring', 'amount' => 5000, 'period_value' => 1, 'period_unit' => 'day'],
            'ads.available' => false,
        ],
        'roles' => ['student'],
    ])
        ->and($demo->switchableRoles())->toBe(['student', 'lecturer'])
        ->and($demo->isSpecial())->toBeTrue();
});

test('a feature set to an amount needs the amount', function () {
    seedPlansForSpecial();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Demo',
            'email' => 'demo@pb.edu.bn',
            'role' => 'lecturer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'special_enabled' => '1',
            'special' => ['features' => [featureId('cpbn_bot.token_quota') => ['custom' => '1', 'mode' => 'total', 'amount' => '']]],
        ])
        ->assertSessionHasErrors('special.features.'.featureId('cpbn_bot.token_quota').'.amount');
});

test('unticking Special account makes it ordinary again', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $lecturer = User::factory()->create(['role' => 'lecturer', 'email' => 'plain@pb.edu.bn']);
    $lecturer->forceFill(['special_rules' => ['plan' => 'premium', 'features' => [], 'roles' => []]])->save();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $lecturer->id), [
            'name' => $lecturer->name,
            'email' => 'plain@pb.edu.bn',
            'role' => 'lecturer',
            'phone' => '712 3457',
            'phone_country' => 'BN',
            'special_enabled' => '0',
            'special' => ['plan' => 'premium'],
        ])
        ->assertSessionHasNoErrors();

    expect($lecturer->fresh()->special_rules)->toBeNull();
});

test('a special account can be put on a plan of its own', function () {
    seedPlansForSpecial();
    $student = specialStudent(['plan' => 'premium']);

    $access = app(EntitlementService::class)->accessFor($student);

    expect($access['source'])->toBe('special')
        ->and($access['plan']->code)->toBe('premium');
});

test('an allowance set for one account replaces the plan\'s, unlimited or an amount', function () {
    seedPlansForSpecial();

    $unlimited = specialStudent(['features' => ['cpbn_bot.token_quota' => ['mode' => 'unlimited']]]);
    $daily = specialStudent(['features' => ['cpbn_bot.token_quota' => ['mode' => 'recurring', 'amount' => 5000, 'period_value' => 1, 'period_unit' => 'day']]]);

    $usage = app(FeatureUsageService::class);

    expect($usage->status($unlimited, 'cpbn_bot.token_quota')['mode'])->toBe('unlimited')
        ->and($usage->status($daily, 'cpbn_bot.token_quota')['amount'])->toBe(5000);
});

test('a feature switched off for one account is off, whatever the plan says', function () {
    seedPlansForSpecial();
    $student = specialStudent(['features' => ['ads.available' => false]]);

    expect(app(EntitlementService::class)->shouldShowAds($student))->toBeFalse();
});

test('an admin can set up their own student side, or leave it following the plans', function () {
    seedPlansForSpecial();
    $admin = User::factory()->create(['role' => 'admin', 'email' => 'boss@pb.edu.bn']);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $admin->id))
        ->assertOk()
        ->assertSee('Set up my own student side');

    expect(app(EntitlementService::class)->accessFor($admin)['source'])->not->toBe('special');

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin->id), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'admin',
            'special_enabled' => '1',
            'special' => [
                'plan' => 'premium',
                'features' => [featureId('cpbn_bot.token_quota') => ['custom' => '1', 'mode' => 'unlimited']],
            ],
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($admin->fresh())->post(route('role.switch'), ['role' => 'student']);

    $student = $admin->fresh();

    expect($student->role)->toBe('student')
        ->and(app(EntitlementService::class)->accessFor($student)['plan']->code)->toBe('premium')
        ->and(app(FeatureUsageService::class)->status($student, 'cpbn_bot.token_quota')['mode'])->toBe('unlimited');
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

    $this->actingAs($student)->get(route('lecturer.dashboard'))->assertOk()->assertSee('Back to Student');

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

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('View as Student');

    $this->actingAs($admin)->post(route('role.switch'), ['role' => 'student']);

    $admin->refresh();

    expect($admin->role)->toBe('student')
        ->and($admin->homeRole())->toBe('admin')
        /* Staff trying the student side are never asked to confirm an email. */
        ->and($admin->mustVerifyEmailAddress())->toBeFalse();

    $this->actingAs($admin)->get(route('student.settings'))->assertOk()->assertSee('Back to Admin');
    $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();

    $this->actingAs($admin)->post(route('role.switch'), ['role' => 'admin']);

    expect($admin->fresh()->role)->toBe('admin');
});

test('an ordinary student sees no role switcher', function () {
    seedPlansForSpecial();
    $student = User::factory()->create(['role' => 'student']);

    $this->actingAs($student)->get(route('student.settings'))->assertOk()->assertDontSee('View as')->assertDontSee('Back to Admin');
});
