<?php

use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedPlansForGrants(): void
{
    test()->seed(FeatureDefinitionSeeder::class);
    test()->seed(PlanSeeder::class);
}

function grantAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

test(
    'an admin can grant a plan to one account',
    function () {
        seedPlansForGrants();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $premium = Plan::where('code', 'premium')->firstOrFail();

        $this->actingAs(grantAdmin())
            ->post(
                route('admin.business.grants.store'),
                [
                    'user_id' => $student->id,
                    'plan_id' => $premium->id,
                    'source' => 'admin',
                ]
            )
            ->assertRedirect(
                route('admin.business.grants.index')
            );

        $grant = UserPlanGrant::firstOrFail();

        expect($grant->user_id)
            ->toBe($student->id)
            ->and($grant->is_active)
            ->toBeTrue();

        /*
         * The point of the screen: the account now resolves to
         * the granted plan rather than the default one.
         */
        expect(
            app(EntitlementService::class)
                ->planFor($student->fresh())
                ->code
        )->toBe('premium');
    }
);

test(
    'revoking keeps the record rather than deleting it',
    function () {
        seedPlansForGrants();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $grant = UserPlanGrant::create([
            'user_id' => $student->id,
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'source' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs(grantAdmin())
            ->put(
                route('admin.business.grants.revoke', $grant)
            )
            ->assertRedirect();

        $grant->refresh();

        /*
         * Kept so there is a trace of who was given what, and
         * when it was taken away.
         */
        expect(UserPlanGrant::count())
            ->toBe(1)
            ->and($grant->is_active)
            ->toBeFalse()
            ->and($grant->ends_at)
            ->not->toBeNull();
    }
);

test(
    'an end date before the start date is rejected',
    function () {
        seedPlansForGrants();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $this->actingAs(grantAdmin())
            ->post(
                route('admin.business.grants.store'),
                [
                    'user_id' => $student->id,
                    'plan_id' => Plan::where('code', 'premium')->value('id'),
                    'source' => 'admin',
                    'starts_at' => now()->addWeek()->toDateString(),
                    'ends_at' => now()->toDateString(),
                ]
            )
            ->assertSessionHasErrors('ends_at');

        expect(UserPlanGrant::count())->toBe(0);
    }
);

test(
    'accounts with a live grant are not offered again',
    function () {
        seedPlansForGrants();

        $granted = User::factory()->create([
            'role' => 'student',
            'name' => 'Already Granted',
        ]);

        $ungranted = User::factory()->create([
            'role' => 'student',
            'name' => 'Not Yet Granted',
        ]);

        UserPlanGrant::create([
            'user_id' => $granted->id,
            'plan_id' => Plan::where('code', 'premium')->value('id'),
            'source' => 'admin',
            'is_active' => true,
        ]);

        /*
         * Two live grants on one account would leave nobody
         * able to say which is in force.
         */
        $this->actingAs(grantAdmin())
            ->get(route('admin.business.grants.index'))
            ->assertOk()
            ->assertSee('Not Yet Granted')
            ->assertDontSee('Already Granted —', false);
    }
);

test(
    'a lecturer cannot grant access',
    function () {
        seedPlansForGrants();

        $lecturer = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $this->actingAs($lecturer)
            ->get(route('admin.business.grants.index'))
            ->assertForbidden();
    }
);
