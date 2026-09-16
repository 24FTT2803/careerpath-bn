<?php

use App\Models\Advertisement;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\Plan;
use App\Models\User;
use App\Services\Business\AdvertisementService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedPlansForAds(): void
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

function adService(): AdvertisementService
{
    return app(AdvertisementService::class);
}

/**
 * Put a user on a named plan.
 */
function placeOnPlan(User $user, string $code): void
{
    $user->planGrants()->create([
        'plan_id' => Plan::where('code', $code)->value('id'),
        'source' => 'admin',
        'is_active' => true,
    ]);
}

function makeAd(array $attributes = []): Advertisement
{
    return Advertisement::create(array_merge([
        'title' => 'Test advertisement',
        'type' => Advertisement::TYPE_IMAGE,
        'asset_path' => 'ads/test.png',
        'position' => Advertisement::POSITION_ONE,
        'is_active' => true,
    ], $attributes));
}

test(
    'an opted in student sees an advertisement in each position',
    function () {
        seedPlansForAds();

        /*
         * Advertising is opt in for every student, so the
         * preference is what turns it on rather than the plan.
         */
        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        makeAd(['position' => Advertisement::POSITION_ONE]);
        makeAd(['position' => Advertisement::POSITION_TWO]);

        $resolved = adService()->forStudent($student);

        expect($resolved)->toHaveCount(2)
            ->and($resolved->has(Advertisement::POSITION_ONE))
            ->toBeTrue()
            ->and($resolved->has(Advertisement::POSITION_TWO))
            ->toBeTrue();
    }
);

test(
    'a premium student sees no advertisements by default',
    function () {
        seedPlansForAds();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        placeOnPlan($student, 'premium');

        makeAd();

        /*
         * Premium is ad free, and show_ads defaults to false, so
         * a premium student who has never visited their settings
         * sees nothing.
         */
        expect(
            adService()->forStudent($student->fresh())
        )->toBeEmpty();
    }
);

test(
    'a premium student can opt in to advertisements',
    function () {
        seedPlansForAds();

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        placeOnPlan($student, 'premium');

        makeAd();

        /*
         * Opting in is the student's choice, so it overrides an
         * ad-free plan rather than being overridden by it.
         */
        expect(
            adService()->forStudent($student->fresh())
        )->toHaveCount(1);
    }
);

test(
    'staff never see advertisements',
    function () {
        seedPlansForAds();

        $lecturer = User::factory()->create([
            'role' => 'lecturer',
            'show_ads' => true,
        ]);

        makeAd();

        expect(
            adService()->forStudent($lecturer)
        )->toBeEmpty();
    }
);

test(
    'an inactive or expired advertisement is never shown',
    function () {
        seedPlansForAds();

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        makeAd([
            'position' => Advertisement::POSITION_ONE,
            'is_active' => false,
        ]);

        makeAd([
            'position' => Advertisement::POSITION_TWO,
            'ends_at' => now()->subDay(),
        ]);

        expect(
            adService()->forStudent($student)
        )->toBeEmpty();
    }
);

test(
    'an advertisement scheduled for later is not shown yet',
    function () {
        seedPlansForAds();

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        makeAd([
            'starts_at' => now()->addWeek(),
        ]);

        expect(
            adService()->forStudent($student)
        )->toBeEmpty();
    }
);

test(
    'a targeted advertisement is preferred for its group',
    function () {
        seedPlansForAds();
        test()->seed(OrganisationGroupSeeder::class);

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        $group = OrganisationGroup::first();

        $student->groupMemberships()->create([
            'organisation_group_id' => $group->id,
        ]);

        makeAd([
            'title' => 'Everyone',
        ]);

        makeAd([
            'title' => 'Just this cohort',
            'organisation_group_id' => $group->id,
        ]);

        $resolved = adService()->forStudent($student->fresh());

        expect(
            $resolved->get(Advertisement::POSITION_ONE)->title
        )->toBe('Just this cohort');
    }
);

test(
    'an advertisement targeted elsewhere is not shown',
    function () {
        seedPlansForAds();
        test()->seed(OrganisationGroupSeeder::class);

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        makeAd([
            'title' => 'Another cohort',
            'organisation_group_id' => OrganisationGroup::first()->id,
        ]);

        expect(
            adService()->forStudent($student)
        )->toBeEmpty();
    }
);

test(
    'an advertisement aimed at a school reaches its classes',
    function () {
        seedPlansForAds();
        test()->seed(OrganisationGroupSeeder::class);

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        $class = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Class / Group')
            )
            ->firstOrFail();

        $school = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'School')
            )
            ->firstOrFail();

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        makeAd([
            'title' => 'School wide',
            'organisation_group_id' => $school->id,
        ]);

        /*
         * Students belong to a class, never to the school
         * directly, so this only works if targeting reaches
         * through the structure.
         */
        expect(
            adService()->forStudent($student->fresh())
        )->toHaveCount(1);
    }
);

test(
    'an advertisement aimed at an intake reaches the same class',
    function () {
        seedPlansForAds();
        test()->seed(OrganisationGroupSeeder::class);

        $student = User::factory()->create([
            'role' => 'student',
            'show_ads' => true,
        ]);

        $class = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Class / Group')
            )
            ->firstOrFail();

        $intake = OrganisationGroup::create([
            'organisation_id' => $class->organisation_id,
            'group_type_id' => OrganisationGroupType::where(
                'name',
                'Intake'
            )->value('id'),
            'name' => 'Intake 14',
            'is_active' => true,
        ]);

        $class->parents()->attach(
            $intake->id,
            ['is_primary' => false]
        );

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        makeAd([
            'title' => 'January starters',
            'organisation_group_id' => $intake->id,
        ]);

        /*
         * The second branch. Reaching this student through the
         * intake is what multiple parents bought us.
         */
        expect(
            adService()->forStudent($student->fresh())
        )->toHaveCount(1);
    }
);

test(
    'a free student sees advertisements without opting in',
    function () {
        seedPlansForAds();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        makeAd();

        /*
         * The free plan carries advertising, so nothing has to
         * be switched on for it to appear.
         */
        expect(
            adService()->forStudent($student)
        )->toHaveCount(1);
    }
);
