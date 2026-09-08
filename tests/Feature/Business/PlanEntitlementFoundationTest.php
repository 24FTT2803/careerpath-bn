<?php

use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed(
        PlanSeeder::class
    );
});

test('users without a grant receive the default free plan', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $service = app(
        EntitlementService::class
    );

    $plan = $service->planFor(
        $user
    );

    expect($plan)
        ->not->toBeNull()
        ->and($plan->code)
        ->toBe('free')
        ->and(
            $service->allows(
                $user,
                'career_recommendations.enabled'
            )
        )
        ->toBeTrue()
        ->and(
            $service->value(
                $user,
                'career_recommendations.generation_limit'
            )
        )
        ->toBe(3);
});

test('an active user plan grant overrides the default plan', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    UserPlanGrant::create([
        'user_id' => $user->id,
        'plan_id' => $premium->id,
        'source' => 'admin',
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($user)?->code
    )
        ->toBe('premium')
        ->and(
            $service->value(
                $user,
                'career_recommendations.generation_limit'
            )
        )
        ->toBeNull();
});

test('inactive grants do not override the default plan', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    UserPlanGrant::create([
        'user_id' => $user->id,
        'plan_id' => $premium->id,
        'source' => 'admin',
        'is_active' => false,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($user)?->code
    )->toBe('free');
});

test('expired grants do not override the default plan', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    UserPlanGrant::create([
        'user_id' => $user->id,
        'plan_id' => $premium->id,
        'source' => 'admin',
        'ends_at' => now()->subDay(),
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($user)?->code
    )->toBe('free');
});

test('future grants do not apply before their start date', function () {
    $user = User::factory()->create([
        'role' => 'student',
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    UserPlanGrant::create([
        'user_id' => $user->id,
        'plan_id' => $premium->id,
        'source' => 'admin',
        'starts_at' => now()->addDay(),
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($user)?->code
    )->toBe('free');
});

test('student advertisements are opt in and disabled by default', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $service = app(
        EntitlementService::class
    );

    $student->refresh();

    expect($student->show_ads)
        ->toBeFalse()
        ->and(
            $service->shouldShowAds(
                $student
            )
        )
        ->toBeFalse();

    $student->update([
        'show_ads' => true,
    ]);

    $student->refresh();

    expect(
        $service->shouldShowAds(
            $student
        )
    )->toBeTrue();
});

test('premium students can also choose whether advertisements are shown', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'show_ads' => true,
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $premium->id,
        'source' => 'admin',
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->shouldShowAds(
            $student
        )
    )->toBeTrue();

    $student->update([
        'show_ads' => false,
    ]);

    $student->refresh();

    expect(
        $service->shouldShowAds(
            $student
        )
    )->toBeFalse();
});

test('staff accounts do not show advertisements even when the preference is enabled', function (
    string $role
) {
    $user = User::factory()->create([
        'role' => $role,
        'show_ads' => true,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->shouldShowAds(
            $user
        )
    )->toBeFalse();
})->with([
    'lecturer',
    'admin',
]);

test('plan feature seeding is idempotent', function () {
    $this->seed(
        PlanSeeder::class
    );

    $this->seed(
        PlanSeeder::class
    );

    expect(
        Plan::where(
            'code',
            'free'
        )->count()
    )
        ->toBe(1)
        ->and(
            Plan::where(
                'code',
                'premium'
            )->count()
        )
        ->toBe(1)
        ->and(
            Plan::where(
                'code',
                'free'
            )
                ->firstOrFail()
                ->features()
                ->count()
        )
        ->toBe(4)
        ->and(
            Plan::where(
                'code',
                'premium'
            )
                ->firstOrFail()
                ->features()
                ->count()
        )
        ->toBe(4);
});