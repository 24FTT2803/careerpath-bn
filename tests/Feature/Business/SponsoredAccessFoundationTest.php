<?php

use App\Models\BusinessSponsor;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\Plan;
use App\Models\SponsoredAccessGrant;
use App\Models\User;
use App\Models\UserPlanGrant;
use App\Services\Business\EntitlementService;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed(
        PlanSeeder::class
    );
});

function createSponsoredAccessTestStructure(): array
{
    $organisation = Organisation::create([
        'name' => 'Example Institute',
        'code' => 'SPONSOR-TEST',
    ]);

    $schoolType = OrganisationGroupType::create([
        'organisation_id' =>
            $organisation->id,

        'name' => 'School',
    ]);

    $programmeType = OrganisationGroupType::create([
        'organisation_id' =>
            $organisation->id,

        'name' => 'Programme',
    ]);

    $classType = OrganisationGroupType::create([
        'organisation_id' =>
            $organisation->id,

        'name' => 'Class',
    ]);

    $school = OrganisationGroup::create([
        'organisation_id' =>
            $organisation->id,

        'group_type_id' =>
            $schoolType->id,

        'name' => 'Technology School',
        'code' => 'TECH-SCHOOL',
    ]);

    $programme = OrganisationGroup::create([
        'organisation_id' =>
            $organisation->id,

        'group_type_id' =>
            $programmeType->id,

        'parent_id' =>
            $school->id,

        'name' => 'Application Development',
        'code' => 'APP-DEV-TEST',
    ]);

    $firstClass = OrganisationGroup::create([
        'organisation_id' =>
            $organisation->id,

        'group_type_id' =>
            $classType->id,

        'parent_id' =>
            $programme->id,

        'name' => 'DADT04',
        'code' => 'DADT04-TEST',
    ]);

    $secondClass = OrganisationGroup::create([
        'organisation_id' =>
            $organisation->id,

        'group_type_id' =>
            $classType->id,

        'parent_id' =>
            $programme->id,

        'name' => 'DADT05',
        'code' => 'DADT05-TEST',
    ]);

    $sponsor = BusinessSponsor::create([
        'name' => 'Example Sponsor',
        'code' => 'EXAMPLE-SPONSOR',
    ]);

    return compact(
        'organisation',
        'school',
        'programme',
        'firstClass',
        'secondClass',
        'sponsor'
    );
}

test('organisation wide sponsorship applies to organisation members', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' => $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )->toBe('premium');
});

test('parent group sponsorship applies to members of descendant groups', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'organisation_group_id' =>
            $structure['school']->id,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )
        ->toBe('premium')
        ->and(
            $service
                ->sponsoredGrantFor(
                    $student
                )
                ?->organisation_group_id
        )
        ->toBe(
            $structure['school']->id
        );
});

test('sponsorship for an unrelated group does not apply', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    /*
     * Put this group outside the student's
     * ancestor chain.
     */
    $otherProgrammeType =
        OrganisationGroupType::create([
            'organisation_id' =>
                $structure['organisation']->id,

            'name' => 'Other Programme Type',
        ]);

    $otherProgramme =
        OrganisationGroup::create([
            'organisation_id' =>
                $structure['organisation']->id,

            'group_type_id' =>
                $otherProgrammeType->id,

            'parent_id' =>
                $structure['school']->id,

            'name' => 'Data Analytics',
            'code' => 'DATA-TEST',
        ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'organisation_group_id' =>
            $otherProgramme->id,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )->toBe('free');
});

test('inactive sponsors do not provide sponsored access', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $structure['sponsor']->update([
        'is_active' => false,
    ]);

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )->toBe('free');
});

test('expired sponsored grants do not provide access', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'ends_at' =>
            now()->subDay(),
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )->toBe('free');
});

test('direct user grants override sponsored access', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,
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
        $service->planFor($student)?->code
    )->toBe('free');
});

test('group specific sponsorship wins over organisation wide sponsorship at equal priority', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $free->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'priority' => 0,
    ]);

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'organisation_group_id' =>
            $structure['programme']->id,

        'priority' => 0,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )->toBe('premium');
});

test('higher priority sponsorship overrides lower priority sponsorship', function () {
    $structure =
        createSponsoredAccessTestStructure();

    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $student
        ->organisationGroups()
        ->attach(
            $structure['firstClass']->id
        );

    $premium = Plan::where(
        'code',
        'premium'
    )->firstOrFail();

    $free = Plan::where(
        'code',
        'free'
    )->firstOrFail();

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $premium->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'organisation_group_id' =>
            $structure['programme']->id,

        'priority' => 0,
    ]);

    SponsoredAccessGrant::create([
        'business_sponsor_id' =>
            $structure['sponsor']->id,

        'plan_id' =>
            $free->id,

        'organisation_id' =>
            $structure['organisation']->id,

        'priority' => 10,
    ]);

    $service = app(
        EntitlementService::class
    );

    expect(
        $service->planFor($student)?->code
    )->toBe('free');
});