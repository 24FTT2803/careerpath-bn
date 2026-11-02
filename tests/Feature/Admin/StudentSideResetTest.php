<?php

use App\Models\CpbnBotConversation;
use App\Models\FeatureUsage;
use App\Models\User;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;

/*
 * Resetting a demo account's student side, so one account can be
 * shown from the start again and again. Only an admin's or a
 * special account's: a real student's work is never wiped.
 */
function accountWithStudentWork(array $attributes): User
{
    $user = User::factory()->create($attributes);

    $user->interests()->create(['interest_name' => 'Programming', 'category' => 'Technology']);
    $user->aspirations()->create(['career_goals' => ['Game Developer']]);

    $conversation = CpbnBotConversation::create(['user_id' => $user->id, 'title' => 'Help']);
    $conversation->messages()->create(['role' => 'user', 'content' => 'Hello']);

    FeatureUsage::create([
        'user_id' => $user->id,
        'feature_key' => 'cpbn_bot.token_quota',
        'quantity' => 500,
        'access_source' => 'default',
        'used_at' => now(),
    ]);

    $user->forceFill(['programme' => 'Diploma in ICT (Application Development)', 'cgpa' => 3.5])->save();

    return $user->fresh();
}

test('an admin can reset their own student side, keeping their account', function () {
    test()->seed(OrganisationGroupSeeder::class);
    $admin = accountWithStudentWork(['role' => 'admin', 'name' => 'Demo Admin']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Reset student side');

    $this->actingAs($admin)
        ->post(route('admin.users.reset-student-side', $admin->id))
        ->assertRedirect(route('admin.users.index'));

    $admin->refresh();

    expect($admin->interests()->count())->toBe(0)
        ->and($admin->aspirations()->exists())->toBeFalse()
        ->and($admin->cpbnBotConversations()->count())->toBe(0)
        ->and(FeatureUsage::where('user_id', $admin->id)->count())->toBe(0)
        ->and($admin->programme)->toBeNull()
        ->and($admin->cgpa)->toBeNull()
        /* The account itself is untouched. */
        ->and($admin->role)->toBe('admin')
        ->and($admin->name)->toBe('Demo Admin')
        ->and($admin->profile()->exists())->toBeTrue();
});

test('a special account can be reset by an admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $demo = accountWithStudentWork(['role' => 'student']);
    $demo->forceFill(['special_rules' => ['plan' => null, 'features' => [], 'roles' => ['lecturer']]])->save();

    $this->actingAs($admin)
        ->post(route('admin.users.reset-student-side', $demo->id))
        ->assertRedirect(route('admin.users.index'));

    expect($demo->fresh()->interests()->count())->toBe(0)
        ->and($demo->fresh()->special_rules)->toBe(['plan' => null, 'features' => [], 'roles' => ['lecturer']]);
});

test('a real student cannot be reset, and is not offered the button', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = accountWithStudentWork(['role' => 'student', 'name' => 'Real Student']);

    $this->actingAs($admin)
        ->post(route('admin.users.reset-student-side', $student->id))
        ->assertForbidden();

    expect($student->fresh()->interests()->count())->toBe(1);
});

test('only admins can reset anything', function () {
    $lecturer = User::factory()->create(['role' => 'lecturer']);
    $admin = accountWithStudentWork(['role' => 'admin']);

    $this->actingAs($lecturer)
        ->post(route('admin.users.reset-student-side', $admin->id))
        ->assertForbidden();

    expect($admin->fresh()->interests()->count())->toBe(1);
});

test('after a reset the student side is welcomed like a new account, once', function () {
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);

    $admin = accountWithStudentWork(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.users.reset-student-side', $admin->id));
    $this->actingAs($admin)->post(route('role.switch'), ['role' => 'student']);

    $this->actingAs($admin->fresh())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertSee('This is your dashboard.')
        /* The tips that explain where to start are shown again. */
        ->assertSee("key.indexOf('cpbn-tip:') === 0", false);

    $this->actingAs($admin->fresh())
        ->get(route('student.dashboard'))
        ->assertOk()
        ->assertDontSee('This is your dashboard.');
});
