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
use App\Services\Business\FeatureUsageService;
use App\Services\AI\CareerAdviserService;
use App\Services\AI\CareerRecommendationService;
use Illuminate\Database\Eloquent\Collection;
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

test('successful recommendation generation consumes one quota use', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $recommendationService =
        \Mockery::mock(
            CareerRecommendationService::class
        );

    $recommendationService
        ->shouldReceive('generateFor')
        ->once()
        ->andReturn(
            new Collection()
        );

    app()->instance(
        CareerRecommendationService::class,
        $recommendationService
    );

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
            'success',
            'Career recommendations generated successfully.'
        );

    expect(
        $student
            ->featureUsages()
            ->where(
                'feature_key',
                'career_recommendations.generation_quota'
            )
            ->count()
    )->toBe(1);
});

test('failed recommendation generation does not consume quota', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $recommendationService =
        \Mockery::mock(
            CareerRecommendationService::class
        );

    $recommendationService
        ->shouldReceive('generateFor')
        ->once()
        ->andThrow(
            new \RuntimeException(
                'Simulated recommendation failure.'
            )
        );

    app()->instance(
        CareerRecommendationService::class,
        $recommendationService
    );

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
            'Career recommendations could not be processed. Please try again later.'
        );

    expect(
        $student
            ->featureUsages()
            ->where(
                'feature_key',
                'career_recommendations.generation_quota'
            )
            ->count()
    )->toBe(0);
});

test('recommendation generation is blocked when quota is exhausted', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $usage = app(
        FeatureUsageService::class
    );

    foreach (range(1, 3) as $attempt) {
        $usage->recordUsage(
            $student,
            'career_recommendations.generation_quota'
        );
    }

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
            'Career recommendation generation is unavailable. '
            . 'You have reached the usage limit for this feature. '
            . 'Please try again after your usage window allows another request.'
        );

    expect(
        $student
            ->featureUsages()
            ->where(
                'feature_key',
                'career_recommendations.generation_quota'
            )
            ->count()
    )->toBe(3);
});

test('successful career adviser request consumes one quota use', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $adviser =
        \Mockery::mock(
            CareerAdviserService::class
        );

    $adviser
        ->shouldReceive('ask')
        ->once()
        ->andReturn([
            'schema_version' => '1.0',
            'status' => 'completed',
            'message' => 'Test adviser response.',
        ]);

    app()->instance(
        CareerAdviserService::class,
        $adviser
    );

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
        ->assertOk()
        ->assertJson([
            'status' => 'completed',
            'message' =>
                'Test adviser response.',
        ]);

    expect(
        $student
            ->featureUsages()
            ->where(
                'feature_key',
                'career_adviser.usage_quota'
            )
            ->count()
    )->toBe(1);
});

test('failed career adviser request does not consume quota', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $adviser =
        \Mockery::mock(
            CareerAdviserService::class
        );

    $adviser
        ->shouldReceive('ask')
        ->once()
        ->andThrow(
            new \RuntimeException(
                'Simulated adviser failure.'
            )
        );

    app()->instance(
        CareerAdviserService::class,
        $adviser
    );

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
            'status' => 'error',
        ]);

    expect(
        $student
            ->featureUsages()
            ->where(
                'feature_key',
                'career_adviser.usage_quota'
            )
            ->count()
    )->toBe(0);
});

test('career adviser ask endpoint is blocked when quota is exhausted', function () {
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
            'career_adviser.usage_quota'
        )
        ->firstOrFail()
        ->update([
            'value' => [
                'mode' => 'total',
                'amount' => 1,
            ],
        ]);

    $usage = app(
        FeatureUsageService::class
    );

    $usage->recordUsage(
        $student,
        'career_adviser.usage_quota'
    );

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
        ->assertStatus(429)
        ->assertJson([
            'status' => 'unavailable',
            'reason' => 'quota_exceeded',
        ]);

    expect(
        $student
            ->featureUsages()
            ->where(
                'feature_key',
                'career_adviser.usage_quota'
            )
            ->count()
    )->toBe(1);
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