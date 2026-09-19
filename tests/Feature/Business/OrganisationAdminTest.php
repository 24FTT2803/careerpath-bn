<?php

use App\Models\Organisation;
use App\Models\OrganisationGroup;
use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function organisationAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

test(
    'creating an organisation gives it a root group',
    function () {
        $this->actingAs(organisationAdmin())
            ->post(
                route('admin.business.organisations.store'),
                [
                    'name' => 'Universiti Brunei Darussalam',
                    'code' => 'UBD',
                ]
            )
            ->assertRedirect();

        $organisation = Organisation::where('code', 'UBD')
            ->firstOrFail();

        /*
         * Without this the institution exists twice: once as a
         * row nobody can see, and once as whatever somebody
         * happens to put at the top of the tree.
         */
        $root = OrganisationGroup::where(
            'organisation_id',
            $organisation->id
        )->firstOrFail();

        expect($root->name)
            ->toBe('Universiti Brunei Darussalam')
            ->and($root->parents()->count())
            ->toBe(0);
    }
);

test(
    'an organisation can be renamed',
    function () {
        $organisation = Organisation::create([
            'name' => 'Old Name',
            'is_active' => true,
        ]);

        $this->actingAs(organisationAdmin())
            ->put(
                route(
                    'admin.business.organisations.update',
                    $organisation
                ),
                ['name' => 'New Name']
            )
            ->assertRedirect();

        expect($organisation->fresh()->name)->toBe('New Name');
    }
);

test(
    'an organisation carrying a structure cannot be deleted',
    function () {
        test()->seed(OrganisationGroupSeeder::class);

        $organisation = Organisation::firstOrFail();

        $this->actingAs(organisationAdmin())
            ->delete(
                route(
                    'admin.business.organisations.destroy',
                    $organisation
                )
            )
            ->assertSessionHasErrors('organisation');

        expect($organisation->fresh())->not->toBeNull();
    }
);

test(
    'an organisation with only its own root can be deleted',
    function () {
        $this->actingAs(organisationAdmin())
            ->post(
                route('admin.business.organisations.store'),
                ['name' => 'Temporary Institution']
            );

        $organisation = Organisation::where(
            'name',
            'Temporary Institution'
        )->firstOrFail();

        /*
         * The node created alongside it should not block
         * removing something added by mistake.
         */
        $this->actingAs(organisationAdmin())
            ->delete(
                route(
                    'admin.business.organisations.destroy',
                    $organisation
                )
            )
            ->assertRedirect();

        expect($organisation->fresh())->toBeNull();
    }
);

test(
    'archiving hides an organisation without disturbing it',
    function () {
        test()->seed(OrganisationGroupSeeder::class);

        $organisation = Organisation::firstOrFail();

        $groupCount = $organisation->groups()->count();

        $this->actingAs(organisationAdmin())
            ->put(
                route(
                    'admin.business.organisations.archive',
                    $organisation
                )
            )
            ->assertRedirect();

        /*
         * Archiving is about what an administrator sees in
         * pickers. Nothing inside is switched off.
         */
        expect($organisation->fresh()->is_active)
            ->toBeFalse()
            ->and($organisation->groups()->count())
            ->toBe($groupCount);
    }
);

test(
    'a lecturer cannot manage organisations',
    function () {
        $lecturer = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $this->actingAs($lecturer)
            ->get(route('admin.business.groups.index'))
            ->assertForbidden();
    }
);

test(
    'an organisation root cannot be deleted from the tree',
    function () {
        $this->actingAs(organisationAdmin())
            ->post(
                route('admin.business.organisations.store'),
                ['name' => 'Institute of Technology']
            );

        $organisation = Organisation::where(
            'name',
            'Institute of Technology'
        )->firstOrFail();

        /*
         * Removing it here would leave the institution with no
         * structure and nothing saying so.
         */
        $this->actingAs(organisationAdmin())
            ->delete(
                route(
                    'admin.business.groups.destroy',
                    $organisation->root_group_id
                )
            )
            ->assertSessionHasErrors('group');

        expect($organisation->fresh()->rootGroup)->not->toBeNull();
    }
);

test(
    'renaming an organisation renames its root group',
    function () {
        $this->actingAs(organisationAdmin())
            ->post(
                route('admin.business.organisations.store'),
                ['name' => 'Old Institute']
            );

        $organisation = Organisation::where(
            'name',
            'Old Institute'
        )->firstOrFail();

        $this->actingAs(organisationAdmin())
            ->put(
                route(
                    'admin.business.organisations.update',
                    $organisation
                ),
                ['name' => 'New Institute']
            );

        expect($organisation->fresh()->rootGroup->name)
            ->toBe('New Institute');
    }
);
