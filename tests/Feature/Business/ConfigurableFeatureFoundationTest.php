<?php

use App\Models\BusinessSponsor;
use App\Models\FeatureDefinition;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\SponsoredAccessFeatureOverride;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed([
        FeatureDefinitionSeeder::class,
        PlanSeeder::class,
    ]);
});

function createConfigurableFeatureTestScope(): array
{
    $organisation = Organisation::create([
        'name' => 'Feature Test Institute',
        'code' => 'FEATURE-TEST',
    ]);

    $groupType =
        OrganisationGroupType::create([
            'organisation_id' =>
                $organisation->id,

            'name' =>
                'Class',
        ]);

    $group = OrganisationGroup::create([
        'organisation_id' =>
            $organisation->id,

        'group_type_id' =>
            $groupType->id,

        'name' =>
            'Feature Test Group',

        'code' =>
            'FEATURE-GROUP',
    ]);

    $sponsor = BusinessSponsor::create([
        'name' =>
            'Feature Test Sponsor',

        'code' =>
            'FEATURE-SPONSOR',
    ]);

    return compact(
        'organisation',
        'group',
        'sponsor'
    );
}

test('feature catalogue supports boolean number and quota types', function () {
    expect(
        FeatureDefinition::where(
            'value_type',
            'boolean'
        )->exists()
    )->toBeTrue()
        ->and(
            FeatureDefinition::where(
                'value_type',
                'number'
            )->exists()
        )
        ->toBeTrue()
        ->and(
            FeatureDefinition::where(
                'value_type',
                'quota'
            )->exists()
        )
        ->toBeTrue()
        ->and(
            FeatureDefinition::count()
        )
        ->toBe(21);
});

test('feature catalogue supports parent child feature relationships', function () {
    $matchScore =
        FeatureDefinition::where(
            'key',
            'career_recommendations.detailed_analysis.match_score.enabled'
        )->firstOrFail();

    expect(
        $matchScore->parent_key
    )->toBe(
        'career_recommendations.detailed_analysis.enabled'
    );
});

test('plan quotas support arbitrary amount and time intervals', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::updateOrCreate(
        [
            'plan_id' =>
                $free->id,

            'key' =>
                'career_recommendations.generation_quota',
        ],
        [
            'value' => [
                'mode' =>
                    'recurring',

                'amount' =>
                    100,

                'period_value' =>
                    7,

                'period_unit' =>
                    'day',
            ],
        ]
    );

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->value(
            $student,
            'career_recommendations.generation_quota'
        )
    )->toBe([
        'mode' => 'recurring',
        'amount' => 100,
        'period_value' => 7,
        'period_unit' => 'day',
    ]);
});

test('quota values support total and unlimited modes', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::updateOrCreate(
        [
            'plan_id' =>
                $free->id,

            'key' =>
                'career_recommendations.generation_quota',
        ],
        [
            'value' => [
                'mode' =>
                    'total',

                'amount' =>
                    25,
            ],
        ]
    );

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->value(
            $student,
            'career_recommendations.generation_quota'
        )
    )->toBe([
        'mode' => 'total',
        'amount' => 25,
    ]);

    PlanFeature::updateOrCreate(
        [
            'plan_id' =>
                $free->id,

            'key' =>
                'career_recommendations.generation_quota',
        ],
        [
            'value' => [
                'mode' =>
                    'unlimited',
            ],
        ]
    );

    expect(
        $service->value(
            $student,
            'career_recommendations.generation_quota'
        )
    )->toBe([
        'mode' => 'unlimited',
    ]);
});

test('global maintenance switch disables a feature for everyone', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_adviser.enabled'
    )->update([
        'global_enabled' => false,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->allows(
            $student,
            'career_adviser.enabled'
        )
    )->toBeFalse();
});

test('disabled parent feature disables its child features', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::updateOrCreate(
        [
            'plan_id' =>
                $free->id,

            'key' =>
                'career_recommendations.detailed_analysis.enabled',
        ],
        [
            'value' =>
                false,
        ]
    );

    PlanFeature::updateOrCreate(
        [
            'plan_id' =>
                $free->id,

            'key' =>
                'career_recommendations.detailed_analysis.match_score.enabled',
        ],
        [
            'value' =>
                true,
        ]
    );

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->allows(
            $student,
            'career_recommendations.detailed_analysis.match_score.enabled'
        )
    )->toBeFalse();
});

test('sponsored access can override boolean number and quota features', function () {
    $scope =
        createConfigurableFeatureTestScope();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $scope['group']->id
        );

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $grant = SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $scope['sponsor']->id,

        'plan_id' =>
            $free->id,

        'organisation_id' =>
            $scope['organisation']->id,

        'organisation_group_id' =>
            $scope['group']->id,

        'funding_type' =>
            'partnership',
    ]);

    $overrides = [
        'career_recommendations.download.enabled'
            => true,

        'career_recommendations.result_count'
            => 100,

        'career_recommendations.generation_quota'
            => [
                'mode' =>
                    'recurring',

                'amount' =>
                    5,

                'period_value' =>
                    2,

                'period_unit' =>
                    'hour',
            ],
    ];

    foreach ($overrides as $key => $value) {
        $definition =
            FeatureDefinition::where(
                'key',
                $key
            )->firstOrFail();

        SponsoredAccessFeatureOverride::create([
            'sponsored_access_grant_id' =>
                $grant->id,

            'feature_definition_id' =>
                $definition->id,

            'value' =>
                $value,
        ]);
    }

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->allows(
            $student,
            'career_recommendations.download.enabled'
        )
    )->toBeTrue()
        ->and(
            $service->value(
                $student,
                'career_recommendations.result_count'
            )
        )
        ->toBe(100)
        ->and(
            $service->value(
                $student,
                'career_recommendations.generation_quota'
            )
        )
        ->toBe([
            'mode' => 'recurring',
            'amount' => 5,
            'period_value' => 2,
            'period_unit' => 'hour',
        ]);
});

test('sponsored access inherits plan value when no override exists', function () {
    $scope =
        createConfigurableFeatureTestScope();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $scope['group']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $scope['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $scope['organisation']->id,

        'organisation_group_id' =>
            $scope['group']->id,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->value(
            $student,
            'career_recommendations.result_count'
        )
    )->toBe(5);
});

test('direct user grant takes precedence over sponsored feature overrides', function () {
    $scope =
        createConfigurableFeatureTestScope();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $scope['group']->id
        );

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    $grant = SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $scope['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $scope['organisation']->id,

        'organisation_group_id' =>
            $scope['group']->id,
    ]);

    $definition =
        FeatureDefinition::where(
            'key',
            'career_recommendations.result_count'
        )->firstOrFail();

    SponsoredAccessFeatureOverride::create([
        'sponsored_access_grant_id' =>
            $grant->id,

        'feature_definition_id' =>
            $definition->id,

        'value' =>
            100,
    ]);

    UserPlanGrant::create([
        'user_id' =>
            $student->id,

        'plan_id' =>
            $free->id,

        'source' =>
            'admin',
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->accessFor(
            $student
        )['source']
    )->toBe('direct')
        ->and(
            $service->value(
                $student,
                'career_recommendations.result_count'
            )
        )
        ->toBe(3);
});

test('sponsorship funding type is separate from entitlement', function () {
    $scope =
        createConfigurableFeatureTestScope();

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $grant = SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $scope['sponsor']->id,

        'plan_id' =>
            $free->id,

        'organisation_id' =>
            $scope['organisation']->id,

        'funding_type' =>
            'complimentary',
    ]);

    expect(
        $grant->funding_type
    )->toBe('complimentary');

    $grant->update([
        'funding_type' =>
            'partnership',
    ]);

    expect(
        $grant
            ->fresh()
            ->funding_type
    )->toBe('partnership');
});

test('feature definition seeding is idempotent', function () {
    $before =
        FeatureDefinition::count();

    $this->seed(
        FeatureDefinitionSeeder::class
    );

    $after =
        FeatureDefinition::count();

    expect($after)
        ->toBe($before)
        ->toBe(21);
});

test('feature seeding preserves global maintenance settings', function () {
    $feature = FeatureDefinition::where(
        'key',
        'career_adviser.enabled'
    )->firstOrFail();

    $feature->update([
        'global_enabled' => false,
    ]);

    $this->seed(
        FeatureDefinitionSeeder::class
    );

    expect(
        $feature
            ->fresh()
            ->global_enabled
    )->toBeFalse();
});