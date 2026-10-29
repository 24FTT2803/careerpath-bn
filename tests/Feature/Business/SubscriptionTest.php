<?php

use App\Models\Notification;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use App\Services\Business\PremiumExpiry;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

/*
 * A student's own Premium subscription.
 *
 * It lasts one month and is not renewed. Cancelling ends it at
 * once, not at the end of the month. Premium given by an
 * administrator, a sponsor or a trial is not the student's to
 * cancel.
 */

function subscriber(): User
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);

    return User::factory()->create(['role' => 'student']);
}

function subscribe(User $student): void
{
    test()->actingAs($student)
        ->post(route('student.settings.upgrade'))
        ->assertRedirect(route('student.settings'));
}

function planCodeOf(User $student): ?string
{
    return app(EntitlementService::class)->planFor($student->fresh())?->code;
}

function grantPremiumBy(User $student, string $source): void
{
    $student->planGrants()->create([
        'plan_id' => Plan::where('code', 'premium')->value('id'),
        'source' => $source,
        'is_active' => true,
    ]);
}

test('a subscription ends one month after it starts', function () {
    $student = subscriber();

    subscribe($student);

    $grant = $student->planGrants()->where('source', 'self')->sole();

    expect($grant->ends_at->toDateString())->toBe(now()->addMonth()->toDateString());

    $this->travel(1)->month();
    $this->travel(1)->day();

    expect(planCodeOf($student))->toBe('free');
});

test('the upgrade message gives the end date', function () {
    $student = subscriber();

    $this->actingAs($student)
        ->post(route('student.settings.upgrade'))
        ->assertSessionHas('success', fn (string $message) => str_contains(
            $message,
            'until '.now()->addMonth()->setTimezone(config('app.business_timezone'))->format('j M Y')
        ));
});

test('cancelling ends the subscription straight away', function () {
    $student = subscriber();

    subscribe($student);

    $this->actingAs($student)
        ->post(route('student.settings.cancel-subscription'))
        ->assertRedirect(route('student.settings'))
        ->assertSessionHas('success', fn (string $message) => str_contains($message, 'has ended'));

    expect(planCodeOf($student))->toBe('free');

    $grant = $student->planGrants()->where('source', 'self')->sole();

    expect($grant->is_active)->toBeFalse()
        ->and($grant->ends_at->isFuture())->toBeFalse();
});

test('a cancelled subscription is not announced again as having expired', function () {
    $student = subscriber();

    subscribe($student);

    $this->actingAs($student)->post(route('student.settings.cancel-subscription'));

    $this->travel(2)->months();

    app(PremiumExpiry::class)->run();

    expect(Notification::where('user_id', $student->id)->count())->toBe(0);
});

test('advertising returns once a subscription is cancelled', function () {
    $student = subscriber();

    subscribe($student);

    expect(app(EntitlementService::class)->shouldShowAds($student->fresh()))->toBeFalse();

    $this->actingAs($student)->post(route('student.settings.cancel-subscription'));

    expect(app(EntitlementService::class)->shouldShowAds($student->fresh()))->toBeTrue();
});

test('a student with no subscription has nothing to cancel', function () {
    $student = subscriber();

    $this->actingAs($student)
        ->post(route('student.settings.cancel-subscription'))
        ->assertSessionHasErrors('subscription');
});

test('premium given by an administrator cannot be cancelled by the student', function () {
    $student = subscriber();

    grantPremiumBy($student, 'admin');

    $this->actingAs($student)
        ->post(route('student.settings.cancel-subscription'))
        ->assertSessionHasErrors('subscription');

    expect(planCodeOf($student))->toBe('premium');
});

test('settings shows the end date and a cancel button to a subscriber', function () {
    $student = subscriber();

    subscribe($student);

    $this->actingAs($student)
        ->get(route('student.settings'))
        ->assertOk()
        ->assertSee('Subscription ends')
        ->assertSee(now()->addMonth()->setTimezone(config('app.business_timezone'))->format('j M Y'))
        ->assertSee('id="cancelSubscriptionForm"', false)
        ->assertDontSee('id="upgradeForm"', false);
});

test('settings offers no cancel button for premium the student did not subscribe to', function () {
    $student = subscriber();

    grantPremiumBy($student, 'admin');

    $this->actingAs($student)
        ->get(route('student.settings'))
        ->assertOk()
        ->assertDontSee('id="cancelSubscriptionForm"', false)
        ->assertDontSee('Subscription ends')
        ->assertSee('no subscription to cancel');
});

test('settings offers a one month upgrade on the free plan', function () {
    $student = subscriber();

    $this->actingAs($student)
        ->get(route('student.settings'))
        ->assertOk()
        ->assertSee('id="upgradeForm"', false)
        ->assertSee('A subscription lasts')
        ->assertDontSee('id="cancelSubscriptionForm"', false);
});

test('an expired subscription is announced as a monthly subscription', function () {
    $student = subscriber();

    subscribe($student);

    $this->travel(1)->month();
    $this->travel(1)->day();

    app(PremiumExpiry::class)->run();

    $notice = Notification::where('user_id', $student->id)->sole();

    expect($notice->message)
        ->toContain('Your monthly Premium subscription ended')
        ->toContain('You can subscribe again from Settings.');
});

test('a student can subscribe again after cancelling', function () {
    $student = subscriber();

    subscribe($student);
    $this->actingAs($student)->post(route('student.settings.cancel-subscription'));
    subscribe($student);

    expect(planCodeOf($student))->toBe('premium')
        ->and(UserPlanGrant::where('user_id', $student->id)->currentlyActive()->count())->toBe(1);
});
