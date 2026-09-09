<?php

use App\Models\FeatureDefinition;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed([
        FeatureDefinitionSeeder::class,
        PlanSeeder::class,
    ]);
});

test('admin can view plan and feature management', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(
            route(
                'admin.business.plans.index'
            )
        );

    $response
        ->assertOk()
        ->assertSee(
            'Plans & Features'
        )
        ->assertSee(
            'Global Feature Controls'
        )
        ->assertSee(
            'Free'
        )
        ->assertSee(
            'Premium'
        );
});

test('lecturer cannot access plan and feature management', function () {
    $lecturer = User::factory()->create([
        'role' => 'lecturer',
    ]);

    $this
        ->actingAs($lecturer)
        ->get(
            route(
                'admin.business.plans.index'
            )
        )
        ->assertForbidden();
});

test('student cannot access plan and feature management', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $this
        ->actingAs($student)
        ->get(
            route(
                'admin.business.plans.index'
            )
        )
        ->assertForbidden();
});

test('admin can update a boolean plan feature', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $feature =
        FeatureDefinition::where(
            'key',
            'career_adviser.enabled'
        )->firstOrFail();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.plans.update',
                $free
            ),
            [
                'features' => [
                    $feature->id => [
                        'value' => 0,
                    ],
                ],
            ]
        )
        ->assertRedirect(
            route(
                'admin.business.plans.index'
            )
        )
        ->assertSessionHas('success');

    expect(
        PlanFeature::where(
            'plan_id',
            $free->id
        )
            ->where(
                'key',
                $feature->key
            )
            ->firstOrFail()
            ->value
    )->toBeFalse();
});

test('admin can update a numeric plan feature', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $feature =
        FeatureDefinition::where(
            'key',
            'career_recommendations.result_count'
        )->firstOrFail();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.plans.update',
                $free
            ),
            [
                'features' => [
                    $feature->id => [
                        'value' => 12,
                    ],
                ],
            ]
        )
        ->assertSessionHasNoErrors();

    expect(
        PlanFeature::where(
            'plan_id',
            $free->id
        )
            ->where(
                'key',
                $feature->key
            )
            ->firstOrFail()
            ->value
    )->toBe(12);
});

test('admin can configure an arbitrary recurring quota', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $feature =
        FeatureDefinition::where(
            'key',
            'career_recommendations.generation_quota'
        )->firstOrFail();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.plans.update',
                $free
            ),
            [
                'features' => [
                    $feature->id => [
                        'mode' =>
                            'recurring',

                        'amount' =>
                            100,

                        'period_value' =>
                            7,

                        'period_unit' =>
                            'day',
                    ],
                ],
            ]
        )
        ->assertSessionHasNoErrors();

    expect(
        PlanFeature::where(
            'plan_id',
            $free->id
        )
            ->where(
                'key',
                $feature->key
            )
            ->firstOrFail()
            ->value
    )->toBe([
        'mode' =>
            'recurring',

        'amount' =>
            100,

        'period_value' =>
            7,

        'period_unit' =>
            'day',
    ]);
});

test('admin can configure total and unlimited quotas', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    $feature =
        FeatureDefinition::where(
            'key',
            'career_recommendations.generation_quota'
        )->firstOrFail();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.plans.update',
                $premium
            ),
            [
                'features' => [
                    $feature->id => [
                        'mode' =>
                            'total',

                        'amount' =>
                            25,
                    ],
                ],
            ]
        )
        ->assertSessionHasNoErrors();

    $planFeature =
        PlanFeature::where(
            'plan_id',
            $premium->id
        )
            ->where(
                'key',
                $feature->key
            )
            ->firstOrFail();

    expect(
        $planFeature->value
    )->toBe([
        'mode' => 'total',
        'amount' => 25,
    ]);

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.plans.update',
                $premium
            ),
            [
                'features' => [
                    $feature->id => [
                        'mode' =>
                            'unlimited',
                    ],
                ],
            ]
        )
        ->assertSessionHasNoErrors();

    expect(
        $planFeature
            ->fresh()
            ->value
    )->toBe([
        'mode' => 'unlimited',
    ]);
});

test('admin can globally disable and enable a feature', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $feature =
        FeatureDefinition::where(
            'key',
            'career_adviser.enabled'
        )->firstOrFail();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.features.global',
                $feature
            ),
            [
                'global_enabled' =>
                    0,
            ]
        )
        ->assertSessionHasNoErrors();

    expect(
        $feature
            ->fresh()
            ->global_enabled
    )->toBeFalse();

    $this
        ->actingAs($admin)
        ->put(
            route(
                'admin.business.features.global',
                $feature
            ),
            [
                'global_enabled' =>
                    1,
            ]
        )
        ->assertSessionHasNoErrors();

    expect(
        $feature
            ->fresh()
            ->global_enabled
    )->toBeTrue();
});

test('invalid quota values are rejected without replacing the existing value', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $feature =
        FeatureDefinition::where(
            'key',
            'career_recommendations.generation_quota'
        )->firstOrFail();

    $existing =
        PlanFeature::where(
            'plan_id',
            $free->id
        )
            ->where(
                'key',
                $feature->key
            )
            ->firstOrFail();

    $before =
        $existing->value;

    $this
        ->actingAs($admin)
        ->from(
            route(
                'admin.business.plans.index'
            )
        )
        ->put(
            route(
                'admin.business.plans.update',
                $free
            ),
            [
                'features' => [
                    $feature->id => [
                        'mode' =>
                            'recurring',

                        'amount' =>
                            0,

                        'period_value' =>
                            7,

                        'period_unit' =>
                            'day',
                    ],
                ],
            ]
        )
        ->assertSessionHasErrors();

    expect(
        $existing
            ->fresh()
            ->value
    )->toBe($before);
});

test('lecturer cannot update plan feature configuration', function () {
    $lecturer = User::factory()->create([
        'role' => 'lecturer',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $feature =
        FeatureDefinition::where(
            'key',
            'career_adviser.enabled'
        )->firstOrFail();

    $this
        ->actingAs($lecturer)
        ->put(
            route(
                'admin.business.plans.update',
                $free
            ),
            [
                'features' => [
                    $feature->id => [
                        'value' => 0,
                    ],
                ],
            ]
        )
        ->assertForbidden();
});