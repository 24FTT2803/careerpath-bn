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
use App\Services\Business\EntitlementService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed([
        FeatureDefinitionSeeder::class,
        PlanSeeder::class,
    ]);
});

test('feature access identifies global maintenance', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_adviser.enabled'
    )->update([
        'global_enabled' => false,
    ]);

    $access = app(
        EntitlementService::class
    )->featureAccess(
        $student,
        'career_adviser.enabled'
    );

    expect($access['allowed'])
        ->toBeFalse()
        ->and($access['reason'])
        ->toBe('maintenance')
        ->and($access['message'])
        ->toBe(
            'Temporarily unavailable due to maintenance.'
        );
});

test('feature access identifies plan restrictions', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $free
        ->features()
        ->where(
            'key',
            'career_adviser.enabled'
        )
        ->firstOrFail()
        ->update([
            'value' => false,
        ]);

    $access = app(
        EntitlementService::class
    )->featureAccess(
        $student,
        'career_adviser.enabled'
    );

    expect($access['allowed'])
        ->toBeFalse()
        ->and($access['reason'])
        ->toBe('plan_restriction')
        ->and($access['message'])
        ->toBe(
            'Not included in your current plan.'
        );
});

test('feature access identifies sponsored restrictions', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $organisation =
        Organisation::create([
            'name' =>
                'Sponsored Test Institute',
            'code' =>
                'SPONSORED-TEST',
        ]);

    $groupType =
        OrganisationGroupType::create([
            'organisation_id' =>
                $organisation->id,

            'name' =>
                'Class',
        ]);

    $group =
        OrganisationGroup::create([
            'organisation_id' =>
                $organisation->id,

            'group_type_id' =>
                $groupType->id,

            'name' =>
                'Sponsored Test Group',

            'code' =>
                'SPONSORED-GROUP',
        ]);

    $student
        ->organisationGroups()
        ->attach(
            $group->id
        );

    $sponsor =
        BusinessSponsor::create([
            'name' =>
                'Sponsored Test Business',

            'code' =>
                'SPONSORED-BUSINESS',
        ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    $grant =
        SponsoredAccessGrant::create([
            'business_sponsor_id' =>
                $sponsor->id,

            'plan_id' =>
                $free->id,

            'organisation_id' =>
                $organisation->id,

            'organisation_group_id' =>
                $group->id,

            'funding_type' =>
                'partnership',
        ]);

    $feature =
        FeatureDefinition::where(
            'key',
            'career_adviser.enabled'
        )->firstOrFail();

    SponsoredAccessFeatureOverride::create([
        'sponsored_access_grant_id' =>
            $grant->id,

        'feature_definition_id' =>
            $feature->id,

        'value' =>
            false,
    ]);

    $access = app(
        EntitlementService::class
    )->featureAccess(
        $student,
        'career_adviser.enabled'
    );

    expect($access['allowed'])
        ->toBeFalse()
        ->and($access['reason'])
        ->toBe(
            'sponsored_restriction'
        )
        ->and($access['message'])
        ->toBe(
            'Not included in your current sponsored access.'
        );
});

test('career adviser page remains visible during maintenance', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_adviser.enabled'
    )->update([
        'global_enabled' => false,
    ]);

    $this
        ->actingAs($student)
        ->get(
            route(
                'student.career-adviser'
            )
        )
        ->assertOk()
        ->assertSee('Career Adviser')
        ->assertSee(
            'Temporarily unavailable due to maintenance.'
        );
});

test('career adviser ask endpoint is blocked during maintenance', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_adviser.enabled'
    )->update([
        'global_enabled' => false,
    ]);

    $this
        ->actingAs($student)
        ->postJson(
            route(
                'student.career-adviser.ask'
            ),
            [
                'message' =>
                    'What career suits me?',
            ]
        )
        ->assertStatus(503)
        ->assertJson([
            'status' => 'unavailable',
            'reason' => 'maintenance',
        ]);
});

test('career adviser ask endpoint is blocked by plan restriction', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    PlanFeature::whereHas(
        'plan',
        fn ($query) =>
            $query->where(
                'code',
                'free'
            )
    )
        ->where(
            'key',
            'career_adviser.enabled'
        )
        ->firstOrFail()
        ->update([
            'value' => false,
        ]);

    $this
        ->actingAs($student)
        ->postJson(
            route(
                'student.career-adviser.ask'
            ),
            [
                'message' =>
                    'What career suits me?',
            ]
        )
        ->assertForbidden()
        ->assertJson([
            'status' => 'unavailable',
            'reason' =>
                'plan_restriction',
        ]);
});

test('recommendation generation is blocked during maintenance', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_recommendations.enabled'
    )->update([
        'global_enabled' => false,
    ]);

    $this
        ->actingAs($student)
        ->post(
            route(
                'student.recommendations.generate'
            )
        )
        ->assertRedirect(
            route(
                'student.dashboard'
            )
        )
        ->assertSessionHas(
            'warning',
            'Career recommendation generation is unavailable. Temporarily unavailable due to maintenance.'
        );
});

test('recommendation generation is blocked by plan restriction', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    PlanFeature::whereHas(
        'plan',
        fn ($query) =>
            $query->where(
                'code',
                'free'
            )
    )
        ->where(
            'key',
            'career_recommendations.enabled'
        )
        ->firstOrFail()
        ->update([
            'value' => false,
        ]);

    $this
        ->actingAs($student)
        ->post(
            route(
                'student.recommendations.generate'
            )
        )
        ->assertRedirect(
            route(
                'student.dashboard'
            )
        )
        ->assertSessionHas(
            'warning',
            'Career recommendation generation is unavailable. Not included in your current plan.'
        );
});

test('recommendation analysis is blocked during maintenance', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_recommendations.enabled'
    )->update([
        'global_enabled' => false,
    ]);

    $this
        ->actingAs($student)
        ->get(
            route(
                'student.recommendations.analysis',
                999
            )
        )
        ->assertRedirect(
            route(
                'student.dashboard'
            )
        )
        ->assertSessionHas(
            'warning',
            'Career recommendation generation is unavailable. Temporarily unavailable due to maintenance.'
        );
});

test('dashboard keeps unavailable features visible with indicators', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::whereIn(
        'key',
        [
            'career_adviser.enabled',
            'career_recommendations.enabled',
        ]
    )->update([
        'global_enabled' => false,
    ]);

    $this
        ->actingAs($student)
        ->get(
            route(
                'student.dashboard'
            )
        )
        ->assertOk()
        ->assertSee('Career Adviser')
        ->assertSee(
            'Generate Recommendations'
        )
        ->assertSee('Maintenance')
        ->assertSee(
            'Temporarily unavailable due to maintenance.'
        );
});