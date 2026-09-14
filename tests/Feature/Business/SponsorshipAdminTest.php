<?php

use App\Models\BusinessSponsor;
use App\Models\OrganisationGroup;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Services\Business\EntitlementService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedForSponsorship(): void
{
    test()->seed(OrganisationGroupSeeder::class);
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

function sponsorshipAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

test(
    'an admin can add a sponsor',
    function () {
        seedForSponsorship();

        $this->actingAs(sponsorshipAdmin())
            ->post(
                route('admin.business.sponsorship.sponsors.store'),
                [
                    'name' => 'AITI',
                    'code' => 'AITI',
                ]
            )
            ->assertRedirect(
                route('admin.business.sponsorship.index')
            );

        expect(
            BusinessSponsor::where('name', 'AITI')->exists()
        )->toBeTrue();
    }
);

test(
    'sponsoring a school reaches a student in its class',
    function () {
        seedForSponsorship();

        $sponsor = BusinessSponsor::create([
            'name' => 'AITI',
            'is_active' => true,
        ]);

        $school = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'School')
            )
            ->firstOrFail();

        $class = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Class / Group')
            )
            ->firstOrFail();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        $this->actingAs(sponsorshipAdmin())
            ->post(
                route('admin.business.sponsorship.grants.store'),
                [
                    'business_sponsor_id' => $sponsor->id,
                    'plan_id' => Plan::where('code', 'premium')
                        ->value('id'),
                    'organisation_group_id' => $school->id,
                ]
            )
            ->assertRedirect();

        /*
         * The student belongs to a class, not to the school.
         * This only resolves because sponsorship walks up the
         * structure.
         */
        expect(
            app(EntitlementService::class)
                ->planFor($student->fresh())
                ->code
        )->toBe('premium');
    }
);

test(
    'withdrawing keeps the record and stops the funding',
    function () {
        seedForSponsorship();

        $sponsor = BusinessSponsor::create([
            'name' => 'AITI',
            'is_active' => true,
        ]);

        $group = OrganisationGroup::firstOrFail();

        $grant = SponsoredAccessGrant::create([
            'business_sponsor_id' => $sponsor->id,
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'organisation_id' => $group->organisation_id,
            'organisation_group_id' => $group->id,
            'is_active' => true,
            'priority' => 0,
        ]);

        $this->actingAs(sponsorshipAdmin())
            ->put(
                route(
                    'admin.business.sponsorship.grants.revoke',
                    $grant
                )
            )
            ->assertRedirect();

        $grant->refresh();

        expect(SponsoredAccessGrant::count())
            ->toBe(1)
            ->and($grant->is_active)
            ->toBeFalse()
            ->and($grant->ends_at)
            ->not->toBeNull();
    }
);

test(
    'a sponsor still funding access cannot be deleted',
    function () {
        seedForSponsorship();

        $sponsor = BusinessSponsor::create([
            'name' => 'AITI',
            'is_active' => true,
        ]);

        $group = OrganisationGroup::firstOrFail();

        SponsoredAccessGrant::create([
            'business_sponsor_id' => $sponsor->id,
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'organisation_id' => $group->organisation_id,
            'organisation_group_id' => $group->id,
            'is_active' => true,
            'priority' => 0,
        ]);

        $this->actingAs(sponsorshipAdmin())
            ->delete(
                route(
                    'admin.business.sponsorship.sponsors.destroy',
                    $sponsor
                )
            )
            ->assertSessionHasErrors('sponsor');

        expect($sponsor->fresh())->not->toBeNull();
    }
);

test(
    'an end date before the start date is rejected',
    function () {
        seedForSponsorship();

        $sponsor = BusinessSponsor::create([
            'name' => 'AITI',
            'is_active' => true,
        ]);

        $this->actingAs(sponsorshipAdmin())
            ->post(
                route('admin.business.sponsorship.grants.store'),
                [
                    'business_sponsor_id' => $sponsor->id,
                    'plan_id' => Plan::where('code', 'premium')
                        ->value('id'),
                    'starts_at' => now()->addWeek()->toDateString(),
                    'ends_at' => now()->toDateString(),
                ]
            )
            ->assertSessionHasErrors('ends_at');

        expect(SponsoredAccessGrant::count())->toBe(0);
    }
);

test(
    'a lecturer cannot manage sponsorship',
    function () {
        seedForSponsorship();

        $lecturer = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $this->actingAs($lecturer)
            ->get(route('admin.business.sponsorship.index'))
            ->assertForbidden();
    }
);
