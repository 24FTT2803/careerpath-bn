<?php

use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function moveAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

function groupNamed(string $name): OrganisationGroup
{
    return OrganisationGroup::where('name', $name)->firstOrFail();
}

test(
    'a group can be moved to a different parent',
    function () {
        test()->seed(OrganisationGroupSeeder::class);

        $programme = groupNamed(
            'Diploma in ICT (Data Analytics)'
        );

        $school = groupNamed(
            'School of Information and Communication Technology'
        );

        $newParent = OrganisationGroup::create([
            'organisation_id' => $school->organisation_id,
            'group_type_id' => $school->group_type_id,
            'name' => 'School of Computing',
            'is_active' => true,
        ]);

        $this->actingAs(moveAdmin())
            ->put(
                route('admin.business.groups.move', $programme),
                [
                    'from_parent_id' => $school->id,
                    'to_parent_id' => $newParent->id,
                ]
            )
            ->assertRedirect();

        $parents = $programme->fresh()
            ->parents()
            ->pluck('organisation_groups.id')
            ->all();

        expect($parents)->toBe([$newParent->id]);
    }
);

test(
    'moving keeps the branch it was not moved from',
    function () {
        test()->seed(OrganisationGroupSeeder::class);

        $class = groupNamed('DADT04');
        $programme = $class->primaryParent();

        $intake = OrganisationGroup::create([
            'organisation_id' => $class->organisation_id,
            'group_type_id' => $programme->group_type_id,
            'name' => 'Intake 14',
            'is_active' => true,
        ]);

        $class->parents()->attach(
            $intake->id,
            ['is_primary' => false]
        );

        $elsewhere = OrganisationGroup::create([
            'organisation_id' => $class->organisation_id,
            'group_type_id' => $programme->group_type_id,
            'name' => 'Diploma in Something Else',
            'is_active' => true,
        ]);

        $this->actingAs(moveAdmin())
            ->put(
                route('admin.business.groups.move', $class),
                [
                    'from_parent_id' => $programme->id,
                    'to_parent_id' => $elsewhere->id,
                ]
            )
            ->assertRedirect();

        $parents = $class->fresh()
            ->parents()
            ->pluck('organisation_groups.id')
            ->all();

        /*
         * Only the branch it was moved from changes. The intake
         * had nothing to do with the move.
         */
        expect($parents)
            ->toContain($elsewhere->id)
            ->toContain($intake->id)
            ->not->toContain($programme->id);
    }
);

test(
    'a group cannot be moved inside its own branch',
    function () {
        test()->seed(OrganisationGroupSeeder::class);

        $school = groupNamed(
            'School of Information and Communication Technology'
        );

        $programme = groupNamed(
            'Diploma in ICT (Data Analytics)'
        );

        $this->actingAs(moveAdmin())
            ->put(
                route('admin.business.groups.move', $school),
                ['to_parent_id' => $programme->id]
            )
            ->assertSessionHasErrors('group');

        expect(
            $school->fresh()
                ->parents()
                ->whereKey($programme->id)
                ->exists()
        )->toBeFalse();
    }
);

test(
    'an organisation root cannot be moved',
    function () {
        $this->actingAs(moveAdmin())
            ->post(
                route('admin.business.organisations.store'),
                ['name' => 'Movable Institute']
            );

        $organisation = Organisation::where(
            'name',
            'Movable Institute'
        )->firstOrFail();

        $this->actingAs(moveAdmin())
            ->put(
                route(
                    'admin.business.groups.move',
                    $organisation->root_group_id
                ),
                ['to_parent_id' => null]
            )
            ->assertSessionHasErrors('group');
    }
);

test(
    'a group can be moved to the top level',
    function () {
        test()->seed(OrganisationGroupSeeder::class);

        $programme = groupNamed(
            'Diploma in ICT (Cloud Networking)'
        );

        $school = $programme->primaryParent();

        $this->actingAs(moveAdmin())
            ->put(
                route('admin.business.groups.move', $programme),
                [
                    'from_parent_id' => $school->id,
                    'to_parent_id' => null,
                ]
            )
            ->assertRedirect();

        expect($programme->fresh()->parents()->count())->toBe(0);
    }
);
