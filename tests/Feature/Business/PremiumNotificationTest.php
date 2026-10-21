<?php

use App\Models\BusinessSponsor;
use App\Models\Notification;
use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(OrganisationGroupSeeder::class);
    $this->seed(FeatureDefinitionSeeder::class);
    $this->seed(PlanSeeder::class);

    $this->admin = User::factory()->create(['role' => 'admin']);
});

function premiumStudentIn(?OrganisationGroup $group = null): User
{
    $student = User::factory()->create(['role' => 'student']);

    if ($group !== null) {
        $student->groupMemberships()->create([
            'organisation_group_id' => $group->id,
        ]);
    }

    return $student;
}

function groupOfType(string $type): OrganisationGroup
{
    return OrganisationGroup::query()
        ->whereHas('type', fn ($query) => $query->where('name', $type))
        ->firstOrFail();
}

test('a student is notified when an admin activates premium for them', function () {
    $student = premiumStudentIn();

    $this->actingAs($this->admin)
        ->post(route('admin.business.grants.store'), [
            'user_id' => $student->id,
            'source' => 'admin',
        ])
        ->assertRedirect(route('admin.business.grants.index'));

    $notification = Notification::where('user_id', $student->id)->sole();

    expect($notification->type)->toBe('premium')
        ->and($notification->title)->toBe('Premium activated')
        ->and($notification->message)->toBe(
            'An administrator has activated Premium on your account. Enjoy all Premium features.'
        )
        ->and($notification->is_read)->toBeFalse();

    $this->actingAs($student)
        ->getJson(route('student.notifications.recent'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('notifications.0.type', 'premium')
        ->assertJsonPath('notifications.0.title', 'Premium activated');

    $this->actingAs($student)
        ->get(route('student.notifications'))
        ->assertOk()
        ->assertSee('Premium activated')
        ->assertSee('An administrator has activated Premium on your account.');
});

test('the message says when scheduled premium starts and ends', function () {
    $student = premiumStudentIn();

    $this->actingAs($this->admin)
        ->post(route('admin.business.grants.store'), [
            'user_id' => $student->id,
            'source' => 'admin',
            'starts_at' => now()->addDays(10)->toDateString(),
            'ends_at' => now()->addDays(40)->toDateString(),
        ]);

    $notification = Notification::where('user_id', $student->id)->sole();

    expect($notification->title)->toBe('Premium coming soon')
        ->and($notification->message)->toStartWith('An administrator has scheduled Premium for your account.')
        ->and($notification->message)->toContain('It runs from');
});

test('a trial says it is a trial and shows its end date', function () {
    $student = premiumStudentIn();

    $this->actingAs($this->admin)
        ->post(route('admin.business.grants.store'), [
            'user_id' => $student->id,
            'source' => 'trial',
            'ends_at' => now()->addDays(14)->toDateString(),
        ]);

    expect(Notification::where('user_id', $student->id)->sole()->message)
        ->toStartWith('You have been given a Premium trial. It runs until');
});

test('students can mark a premium notification as read', function () {
    $student = premiumStudentIn();

    $this->actingAs($this->admin)
        ->post(route('admin.business.grants.store'), [
            'user_id' => $student->id,
            'source' => 'admin',
        ]);

    $notification = Notification::where('user_id', $student->id)->sole();

    $this->actingAs($student)
        ->post(route('student.notifications.read', $notification->id))
        ->assertRedirect();

    expect($notification->fresh()->is_read)->toBeTrue();
});

test('mark all read includes premium notifications', function () {
    $student = premiumStudentIn();

    $this->actingAs($this->admin)
        ->post(route('admin.business.grants.store'), [
            'user_id' => $student->id,
            'source' => 'admin',
        ]);

    $this->actingAs($student)
        ->post(route('student.notifications.read-all'));

    expect(Notification::where('user_id', $student->id)->where('is_read', false)->count())->toBe(0);
});

test('sponsoring a school notifies its students, but not students elsewhere', function () {
    $school = groupOfType('School');
    $class = groupOfType('Class / Group');

    $inClass = premiumStudentIn($class);
    $noGroup = premiumStudentIn();

    $sponsor = BusinessSponsor::create(['name' => 'PB', 'is_active' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.business.sponsorship.grants.store'), [
            'business_sponsor_id' => $sponsor->id,
            'organisation_group_id' => $school->id,
        ])
        ->assertRedirect(route('admin.business.sponsorship.index'))
        ->assertSessionHas('success', 'Sponsored access added. 1 student was notified.');

    $notification = Notification::where('user_id', $inClass->id)->sole();

    expect($notification->type)->toBe('premium')
        ->and($notification->title)->toBe('Premium activated')
        ->and($notification->message)->toBe(
            'Premium has been activated on your account, sponsored by PB. Enjoy all Premium features.'
        )
        ->and(Notification::where('user_id', $noGroup->id)->exists())->toBeFalse();
});

test('students who already have premium are not told again', function () {
    $class = groupOfType('Class / Group');

    $alreadyPremium = premiumStudentIn($class);
    $newlyPremium = premiumStudentIn($class);

    UserPlanGrant::create([
        'user_id' => $alreadyPremium->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ]);

    $sponsor = BusinessSponsor::create(['name' => 'AITI', 'is_active' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.business.sponsorship.grants.store'), [
            'business_sponsor_id' => $sponsor->id,
        ]);

    expect(Notification::where('user_id', $alreadyPremium->id)->where('type', 'premium')->exists())->toBeFalse()
        ->and(Notification::where('user_id', $newlyPremium->id)->where('type', 'premium')->exists())->toBeTrue();
});

test('staff are never sent premium notifications', function () {
    $class = groupOfType('Class / Group');
    $lecturer = User::factory()->create(['role' => 'lecturer']);
    $lecturer->groupMemberships()->create(['organisation_group_id' => $class->id]);

    $sponsor = BusinessSponsor::create(['name' => 'PB', 'is_active' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.business.sponsorship.grants.store'), [
            'business_sponsor_id' => $sponsor->id,
        ]);

    expect(Notification::where('user_id', $lecturer->id)->exists())->toBeFalse();
});

function directGrantFor(User $student, array $attributes = []): UserPlanGrant
{
    return UserPlanGrant::create(array_merge([
        'user_id' => $student->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ], $attributes));
}

function sponsorClass(string $name = 'PB'): array
{
    $class = groupOfType('Class / Group');
    $sponsor = BusinessSponsor::create(['name' => $name, 'is_active' => true]);

    return [$class, $sponsor];
}

test('revoking premium tells the student it ended and which plan they are on now', function () {
    $student = premiumStudentIn();
    $grant = directGrantFor($student);

    $this->actingAs($this->admin)
        ->put(route('admin.business.grants.revoke', $grant))
        ->assertRedirect(route('admin.business.grants.index'));

    $notification = Notification::where('user_id', $student->id)->sole();

    expect($notification->type)->toBe('premium')
        ->and($notification->title)->toBe('Premium ended')
        ->and($notification->message)->toBe(
            'An administrator has ended Premium on your account. Your account is now on the Free plan.'
        );
});

test('revoking a trial says the trial ended', function () {
    $student = premiumStudentIn();
    $grant = directGrantFor($student, ['source' => 'trial']);

    $this->actingAs($this->admin)->put(route('admin.business.grants.revoke', $grant));

    expect(Notification::where('user_id', $student->id)->sole()->message)
        ->toStartWith('Your Premium trial has been ended by an administrator.');
});

test('cancelling scheduled premium says it was cancelled', function () {
    $student = premiumStudentIn();
    $grant = directGrantFor($student, ['starts_at' => now()->addDays(5)]);

    $this->actingAs($this->admin)->put(route('admin.business.grants.revoke', $grant));

    expect(Notification::where('user_id', $student->id)->sole()->title)->toBe('Premium cancelled');
});

test('a revoke says premium continues when a sponsor still covers the student', function () {
    [$class, $sponsor] = sponsorClass('PB');
    $student = premiumStudentIn($class);
    $grant = directGrantFor($student);

    SponsoredAccessGrant::create([
        'business_sponsor_id' => $sponsor->id,
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'organisation_id' => $class->organisation_id,
        'is_active' => true,
        'priority' => 0,
    ]);

    $this->actingAs($this->admin)->put(route('admin.business.grants.revoke', $grant));

    $notification = Notification::where('user_id', $student->id)->sole();

    expect($notification->title)->toBe('Premium access changed')
        ->and($notification->message)->toBe(
            'An administrator has ended Premium on your account. You still have Premium through PB.'
        );
});

test('withdrawing a sponsorship tells only the students who lose premium', function () {
    [$class, $sponsor] = sponsorClass('PB');
    $losing = premiumStudentIn($class);
    $keeping = premiumStudentIn($class);
    directGrantFor($keeping);

    $this->actingAs($this->admin)->post(route('admin.business.sponsorship.grants.store'), [
        'business_sponsor_id' => $sponsor->id,
    ]);

    Notification::query()->delete();

    $grant = SponsoredAccessGrant::query()->latest('id')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('admin.business.sponsorship.grants.revoke', $grant))
        ->assertSessionHas('success', 'Sponsored access withdrawn. 1 student was notified.');

    $notification = Notification::where('user_id', $losing->id)->sole();

    expect($notification->title)->toBe('Premium ended')
        ->and($notification->message)->toBe(
            'PB is no longer sponsoring Premium for you. Your account is now on the Free plan.'
        )
        ->and(Notification::where('user_id', $keeping->id)->exists())->toBeFalse();
});

test('suspending a sponsor tells its students, and reactivating tells them again', function () {
    [$class, $sponsor] = sponsorClass('AITI');
    $student = premiumStudentIn($class);

    $this->actingAs($this->admin)->post(route('admin.business.sponsorship.grants.store'), [
        'business_sponsor_id' => $sponsor->id,
    ]);

    Notification::query()->delete();

    $this->actingAs($this->admin)
        ->put(route('admin.business.sponsorship.sponsors.toggle', $sponsor))
        ->assertSessionHas('success', 'Sponsor suspended. 1 student was notified.');

    expect(Notification::where('user_id', $student->id)->sole()->title)->toBe('Premium ended');

    Notification::query()->delete();

    $this->actingAs($this->admin)
        ->put(route('admin.business.sponsorship.sponsors.toggle', $sponsor->fresh()))
        ->assertSessionHas('success', 'Sponsor reactivated. 1 student was notified.');

    expect(Notification::where('user_id', $student->id)->sole()->title)->toBe('Premium activated');
});
