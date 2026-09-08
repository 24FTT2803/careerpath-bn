<?php

use App\Models\GroupMembership;
use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;

test('organisation groups support configurable hierarchical structures', function () {
    $organisation = Organisation::create([
        'name' => 'Example Organisation',
        'code' => 'EXAMPLE',
    ]);

    $divisionType = OrganisationGroupType::create([
        'organisation_id' => $organisation->id,
        'name' => 'Division',
    ]);

    $teamType = OrganisationGroupType::create([
        'organisation_id' => $organisation->id,
        'name' => 'Team',
    ]);

    $division = OrganisationGroup::create([
        'organisation_id' => $organisation->id,
        'group_type_id' => $divisionType->id,
        'name' => 'Technology Division',
        'code' => 'TECH',
    ]);

    $team = OrganisationGroup::create([
        'organisation_id' => $organisation->id,
        'group_type_id' => $teamType->id,
        'parent_id' => $division->id,
        'name' => 'Software Team',
        'code' => 'SOFTWARE',
    ]);

    expect($team->parent->is($division))
        ->toBeTrue()
        ->and($division->children->contains($team))
        ->toBeTrue()
        ->and($team->type->name)
        ->toBe('Team');
});

test('users can belong to multiple organisation groups', function () {
    $organisation = Organisation::create([
        'name' => 'Example Organisation',
        'code' => 'MEMBERSHIP-TEST',
    ]);

    $groupType = OrganisationGroupType::create([
        'organisation_id' => $organisation->id,
        'name' => 'Group',
    ]);

    $firstGroup = OrganisationGroup::create([
        'organisation_id' => $organisation->id,
        'group_type_id' => $groupType->id,
        'name' => 'Group One',
        'code' => 'GROUP-ONE',
    ]);

    $secondGroup = OrganisationGroup::create([
        'organisation_id' => $organisation->id,
        'group_type_id' => $groupType->id,
        'name' => 'Group Two',
        'code' => 'GROUP-TWO',
    ]);

    $user = User::factory()->create([
        'role' => 'lecturer',
    ]);

    $user->organisationGroups()->attach([
        $firstGroup->id,
        $secondGroup->id,
    ]);

    expect(
        $user->organisationGroups()->count()
    )->toBe(2);
});

test('PB group seeder creates DADT04 hierarchy and assigns existing demo student', function () {
    $student = User::factory()->create([
        'name' => 'Sip Spill',
        'email' => '1508user@example.com',
        'role' => 'student',
        'programme' =>
            'Diploma in ICT (Application Development)',
    ]);

    $this->seed(
        OrganisationGroupSeeder::class
    );

    $this->seed(
        OrganisationGroupSeeder::class
    );

    $organisation = Organisation::where(
        'code',
        'PB'
    )->firstOrFail();

    $dadt04 = OrganisationGroup::where(
        'organisation_id',
        $organisation->id
    )
        ->where('code', 'DADT04')
        ->firstOrFail();

    expect($dadt04->name)
        ->toBe('DADT04')
        ->and($dadt04->type->name)
        ->toBe('Class / Group')
        ->and($dadt04->parent->name)
        ->toBe(
            'Diploma in ICT (Application Development)'
        )
        ->and($dadt04->parent->parent->code)
        ->toBe('SICT');

    expect(
        GroupMembership::where(
            'user_id',
            $student->id
        )
            ->where(
                'organisation_group_id',
                $dadt04->id
            )
            ->count()
    )->toBe(1);
});

test('PB group seeder does not require the demo student to exist', function () {
    $this->seed(
        OrganisationGroupSeeder::class
    );

    expect(
        Organisation::where(
            'code',
            'PB'
        )->exists()
    )->toBeTrue()
        ->and(
            OrganisationGroup::where(
                'code',
                'DADT04'
            )->exists()
        )
        ->toBeTrue();
});