<?php

use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\User;
use App\Models\FeatureDefinition;
use App\Models\UserPlanGrant;
use App\Models\BusinessSponsor;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\SponsoredAccessFeatureOverride;
use App\Models\SponsoredAccessGrant;
use App\Services\Business\FeatureUsageService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed([
        FeatureDefinitionSeeder::class,
        PlanSeeder::class,
    ]);
});

test('unlimited quota always allows usage', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::where(
        'plan_id',
        $free->id
    )
        ->where(
            'key',
            'career_recommendations.generation_quota'
        )
        ->firstOrFail()
        ->update([
            'value' => [
                'mode' => 'unlimited',
            ],
        ]);

    $usage = app(
        FeatureUsageService::class
    );

    foreach (range(1, 10) as $attempt) {
        expect(
            $usage->status(
                $student,
                'career_recommendations.generation_quota'
            )['allowed']
        )->toBeTrue();

        $usage->recordUsage(
            $student,
            'career_recommendations.generation_quota'
        );
    }

    expect(
        $usage->status(
            $student,
            'career_recommendations.generation_quota'
        )['used']
    )->toBe(10);
});

test('total quota blocks after configured amount', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::where(
        'plan_id',
        $free->id
    )
        ->where(
            'key',
            'career_recommendations.generation_quota'
        )
        ->firstOrFail()
        ->update([
            'value' => [
                'mode' => 'total',
                'amount' => 3,
            ],
        ]);

    $usage = app(
        FeatureUsageService::class
    );

    foreach (range(1, 3) as $attempt) {
        expect(
            $usage->status(
                $student,
                'career_recommendations.generation_quota'
            )['allowed']
        )->toBeTrue();

        $usage->recordUsage(
            $student,
            'career_recommendations.generation_quota'
        );
    }

    $status = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($status['allowed'])
        ->toBeFalse()
        ->and($status['reason'])
        ->toBe('quota_exceeded')
        ->and($status['used'])
        ->toBe(3)
        ->and($status['remaining'])
        ->toBe(0);
});

test('quota feature maintenance is preserved as maintenance', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    FeatureDefinition::where(
        'key',
        'career_recommendations.generation_quota'
    )->update([
        'global_enabled' => false,
    ]);

    $status = app(
        FeatureUsageService::class
    )->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($status['allowed'])
        ->toBeFalse()
        ->and($status['reason'])
        ->toBe('maintenance')
        ->and($status['mode'])
        ->toBe('unavailable')
        ->and($status['message'])
        ->toBe(
            'Temporarily unavailable due to maintenance.'
        );
});

test('recurring quota blocks within its configured window', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::where(
        'plan_id',
        $free->id
    )
        ->where(
            'key',
            'career_recommendations.generation_quota'
        )
        ->firstOrFail()
        ->update([
            'value' => [
                'mode' => 'recurring',
                'amount' => 3,
                'period_value' => 7,
                'period_unit' => 'day',
            ],
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

    $status = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($status['allowed'])
        ->toBeFalse()
        ->and($status['used'])
        ->toBe(3)
        ->and($status['remaining'])
        ->toBe(0)
        ->and($status['next_available_at'])
        ->not->toBeNull();
});

test('recurring quota becomes available after the interval', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    PlanFeature::where(
        'plan_id',
        $free->id
    )
        ->where(
            'key',
            'career_recommendations.generation_quota'
        )
        ->firstOrFail()
        ->update([
            'value' => [
                'mode' => 'recurring',
                'amount' => 2,
                'period_value' => 7,
                'period_unit' => 'day',
            ],
        ]);

    $usage = app(
        FeatureUsageService::class
    );

    $usage->recordUsage(
        $student,
        'career_recommendations.generation_quota'
    );

    $usage->recordUsage(
        $student,
        'career_recommendations.generation_quota'
    );

    expect(
        $usage->status(
            $student,
            'career_recommendations.generation_quota'
        )['allowed']
    )->toBeFalse();

    $this->travel(8)->days();

    $status = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($status['allowed'])
        ->toBeTrue()
        ->and($status['used'])
        ->toBe(0)
        ->and($status['remaining'])
        ->toBe(2);
});

test('usage is isolated between students', function () {
    $firstStudent = User::factory()->create([
        'role' => 'student',
    ]);

    $secondStudent = User::factory()->create([
        'role' => 'student',
    ]);

    $usage = app(
        FeatureUsageService::class
    );

    foreach (range(1, 3) as $attempt) {
        $usage->recordUsage(
            $firstStudent,
            'career_recommendations.generation_quota'
        );
    }

    expect(
        $usage->status(
            $firstStudent,
            'career_recommendations.generation_quota'
        )['allowed']
    )->toBeFalse();

    expect(
        $usage->status(
            $secondStudent,
            'career_recommendations.generation_quota'
        )['allowed']
    )->toBeTrue();
});

test(
    'recurring quota supports every configurable interval unit',
    function (
        string $periodUnit,
        string $travelMethod
    ) {
        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $free = Plan::where(
            'code',
            'free'
        )->firstOrFail();

        PlanFeature::where(
            'plan_id',
            $free->id
        )
            ->where(
                'key',
                'career_recommendations.generation_quota'
            )
            ->firstOrFail()
            ->update([
                'value' => [
                    'mode' => 'recurring',
                    'amount' => 1,
                    'period_value' => 2,
                    'period_unit' => $periodUnit,
                ],
            ]);

        $usage = app(
            FeatureUsageService::class
        );

        $usage->recordUsage(
            $student,
            'career_recommendations.generation_quota'
        );

        expect(
            $usage->status(
                $student,
                'career_recommendations.generation_quota'
            )['allowed']
        )->toBeFalse();

        $this
            ->travel(3)
            ->{$travelMethod}();

        $status = $usage->status(
            $student,
            'career_recommendations.generation_quota'
        );

        expect($status['allowed'])
            ->toBeTrue()
            ->and($status['used'])
            ->toBe(0)
            ->and($status['remaining'])
            ->toBe(1);
    }
)->with([
    'minutes' => ['minute', 'minutes'],
    'hours' => ['hour', 'hours'],
    'days' => ['day', 'days'],
    'weeks' => ['week', 'weeks'],
    'months' => ['month', 'months'],
    'years' => ['year', 'years'],
]);

test('usage from a direct plan grant does not leak into default plan usage', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    $grant = UserPlanGrant::create([
        'user_id' => $student->id,
        'plan_id' => $premium->id,
        'source' => 'admin',
        'is_active' => true,
    ]);

    $usage = app(
        FeatureUsageService::class
    );

    $usage->recordUsage(
        $student,
        'career_recommendations.generation_quota'
    );

    $premiumStatus = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($premiumStatus['mode'])
        ->toBe('unlimited')
        ->and($premiumStatus['used'])
        ->toBe(1);

    $grant->update([
        'is_active' => false,
    ]);

    $freeStatus = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($freeStatus['mode'])
        ->toBe('recurring')
        ->and($freeStatus['used'])
        ->toBe(0)
        ->and($freeStatus['remaining'])
        ->toBe(3);
});

test('sponsored quota override is enforced at runtime', function () {
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $organisation = Organisation::create([
        'name' => 'Quota Test Institute',
        'code' => 'QUOTA-TEST',
    ]);

    $groupType = OrganisationGroupType::create([
        'organisation_id' => $organisation->id,
        'name' => 'Class',
    ]);

    $group = OrganisationGroup::create([
        'organisation_id' => $organisation->id,
        'group_type_id' => $groupType->id,
        'name' => 'Quota Test Group',
        'code' => 'QUOTA-GROUP',
    ]);

    $student
        ->organisationGroups()
        ->attach($group->id);

    $sponsor = BusinessSponsor::create([
        'name' => 'Quota Test Sponsor',
        'code' => 'QUOTA-SPONSOR',
    ]);

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    /*
     * The normal Free quota is currently 3 recurring
     * generations. This sponsorship overrides it with
     * a total allowance of only 1.
     */
    $grant = SponsoredAccessGrant::create([
        'business_sponsor_id' => $sponsor->id,
        'plan_id' => $free->id,
        'organisation_id' => $organisation->id,
        'organisation_group_id' => $group->id,
        'funding_type' => 'partnership',
        'is_active' => true,
    ]);

    $quotaDefinition = FeatureDefinition::where(
        'key',
        'career_recommendations.generation_quota'
    )->firstOrFail();

    SponsoredAccessFeatureOverride::create([
        'sponsored_access_grant_id' => $grant->id,
        'feature_definition_id' => $quotaDefinition->id,
        'value' => [
            'mode' => 'total',
            'amount' => 1,
        ],
    ]);

    $usage = app(
        FeatureUsageService::class
    );

    $before = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($before['allowed'])
        ->toBeTrue()
        ->and($before['mode'])
        ->toBe('total')
        ->and($before['amount'])
        ->toBe(1)
        ->and($before['remaining'])
        ->toBe(1);

    $usage->recordUsage(
        $student,
        'career_recommendations.generation_quota'
    );

    $after = $usage->status(
        $student,
        'career_recommendations.generation_quota'
    );

    expect($after['allowed'])
        ->toBeFalse()
        ->and($after['reason'])
        ->toBe('quota_exceeded')
        ->and($after['used'])
        ->toBe(1)
        ->and($after['remaining'])
        ->toBe(0);

    $record = $student
        ->featureUsages()
        ->where(
            'feature_key',
            'career_recommendations.generation_quota'
        )
        ->firstOrFail();

    expect($record->access_source)
        ->toBe('sponsored')
        ->and($record->plan_id)
        ->toBe($free->id)
        ->and($record->grant_id)
        ->toBe($grant->id);
});