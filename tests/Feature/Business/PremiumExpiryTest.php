<?php

use App\Models\BusinessSponsor;
use App\Models\Notification;
use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\PremiumExpiry;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(OrganisationGroupSeeder::class);
    $this->seed(FeatureDefinitionSeeder::class);
    $this->seed(PlanSeeder::class);

    $this->premiumId = Plan::where('code', 'premium')->value('id');
    $this->class = OrganisationGroup::query()
        ->whereHas('type', fn ($query) => $query->where('name', 'Class / Group'))
        ->firstOrFail();
});

function expiringStudent(?OrganisationGroup $group = null): User
{
    $student = User::factory()->create(['role' => 'student']);

    if ($group !== null) {
        $student->groupMemberships()->create(['organisation_group_id' => $group->id]);
    }

    return $student;
}

test('a student is told when their premium trial runs out', function () {
    $student = expiringStudent();

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $this->premiumId,
        'source' => 'trial',
        'is_active' => true,
        'ends_at' => now()->addDay(),
    ]);

    expect(app(PremiumExpiry::class)->run())->toBe(0);

    $this->travel(2)->days();

    expect(app(PremiumExpiry::class)->run())->toBe(1);

    $notification = Notification::where('user_id', $student->id)->sole();

    expect($notification->type)->toBe('premium')
        ->and($notification->title)->toBe('Premium trial ended')
        ->and($notification->message)->toStartWith('Your Premium trial ended on ')
        ->and($notification->message)->toEndWith('Your account is now on the Free plan.');

    /* Never announced twice. */
    expect(app(PremiumExpiry::class)->run())->toBe(0)
        ->and(Notification::where('user_id', $student->id)->count())->toBe(1);
});

test('a page load is enough to send it, no scheduled task needed', function () {
    $student = expiringStudent();

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $this->premiumId,
        'source' => 'admin',
        'is_active' => true,
        'ends_at' => now()->subMinute(),
    ]);

    Cache::flush();

    $this->actingAs($student)
        ->getJson(route('student.notifications.recent'))
        ->assertOk()
        ->assertJsonPath('notifications.0.title', 'Premium ended');
});

test('a student who is still sponsored is told premium carries on', function () {
    $student = expiringStudent($this->class);
    $sponsor = BusinessSponsor::create(['name' => 'PB', 'is_active' => true]);

    SponsoredAccessGrant::create([
        'business_sponsor_id' => $sponsor->id,
        'plan_id' => $this->premiumId,
        'organisation_id' => $this->class->organisation_id,
        'is_active' => true,
        'priority' => 0,
    ]);

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $this->premiumId,
        'source' => 'trial',
        'is_active' => true,
        'ends_at' => now()->subMinute(),
    ]);

    app(PremiumExpiry::class)->run();

    $notification = Notification::where('user_id', $student->id)->sole();

    expect($notification->title)->toBe('Premium access changed')
        ->and($notification->message)->toEndWith('You still have Premium through PB.');
});

test('a revoked grant is not announced again when its date passes', function () {
    $student = expiringStudent();

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $this->premiumId,
        'source' => 'admin',
        'is_active' => false,
        'ends_at' => now()->subMinute(),
    ]);

    expect(app(PremiumExpiry::class)->run())->toBe(0)
        ->and(Notification::count())->toBe(0);
});

test('grants that ended before this update stay quiet', function () {
    $student = expiringStudent();

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $this->premiumId,
        'source' => 'trial',
        'is_active' => true,
        'ends_at' => now()->subMonth(),
        'expiry_notified_at' => now(),
    ]);

    expect(app(PremiumExpiry::class)->run())->toBe(0);
});

test('when a sponsorship ends, only students left without premium are told', function () {
    $losing = expiringStudent($this->class);
    $keeping = expiringStudent($this->class);

    UserPlanGrant::create([
        'user_id' => $keeping->id,
        'plan_id' => $this->premiumId,
        'source' => 'admin',
        'is_active' => true,
    ]);

    $sponsor = BusinessSponsor::create(['name' => 'AITI', 'is_active' => true]);

    SponsoredAccessGrant::create([
        'business_sponsor_id' => $sponsor->id,
        'plan_id' => $this->premiumId,
        'organisation_id' => $this->class->organisation_id,
        'is_active' => true,
        'priority' => 0,
        'ends_at' => now()->subMinute(),
    ]);

    expect(app(PremiumExpiry::class)->run())->toBe(1);

    $notification = Notification::where('user_id', $losing->id)->sole();

    expect($notification->title)->toBe('Premium ended')
        ->and($notification->message)->toStartWith("AITI's sponsorship of your Premium ended on ")
        ->and(Notification::where('user_id', $keeping->id)->exists())->toBeFalse();
});

test('the command can be run by hand or on a schedule', function () {
    $student = expiringStudent();

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $this->premiumId,
        'source' => 'trial',
        'is_active' => true,
        'ends_at' => now()->subMinute(),
    ]);

    $this->artisan('premium:notify-expired')
        ->expectsOutput('1 notification sent.')
        ->assertSuccessful();
});
