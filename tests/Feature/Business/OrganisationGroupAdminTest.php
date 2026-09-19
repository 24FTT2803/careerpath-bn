<?php

use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\User;
use App\Services\Business\EntitlementService;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedGroups(): void
{
    test()->seed(OrganisationGroupSeeder::class);
}

function groupAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

test(
    'an admin can create a group inside another',
    function () {
        seedGroups();

        $programme = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Programme')
            )
            ->firstOrFail();

        $classType = OrganisationGroupType::where(
            'name',
            'Class / Group'
        )->firstOrFail();

        $this->actingAs(groupAdmin())
            ->post(
                route('admin.business.groups.store'),
                [
                    'name' => 'DADT05',
                    'code' => 'DADT05',
                    'group_type_id' => $classType->id,
                    'primary_parent_id' => $programme->id,
                ]
            )
            ->assertRedirect(
                route('admin.business.groups.index', [
                    'organisation' => $programme->organisation_id,
                ])
            );

        $created = OrganisationGroup::where(
            'name',
            'DADT05'
        )->firstOrFail();

        expect(
            $created->parents()->pluck('organisation_groups.id')->all()
        )->toBe([$programme->id]);

        expect($created->is_active)->toBeTrue();
    }
);

test(
    'intake and session types are available',
    function () {
        seedGroups();

        /*
         * Named in the Week 8 structure alongside school,
         * programme and class.
         */
        expect(
            OrganisationGroupType::whereIn(
                'name',
                ['Intake', 'Intake Session']
            )->count()
        )->toBe(2);
    }
);

test(
    'archiving keeps the group and its members',
    function () {
        seedGroups();

        $group = OrganisationGroup::firstOrFail();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $student->groupMemberships()->create([
            'organisation_group_id' => $group->id,
        ]);

        $this->actingAs(groupAdmin())
            ->put(
                route('admin.business.groups.archive', $group)
            )
            ->assertRedirect();

        /*
         * Students belong to these and sponsorships point at
         * them, so archiving must not sever those ties.
         */
        expect($group->fresh()->is_active)
            ->toBeFalse()
            ->and($group->memberships()->count())
            ->toBe(1);
    }
);

test(
    'an archived group can be restored',
    function () {
        seedGroups();

        $group = OrganisationGroup::firstOrFail();
        $group->update(['is_active' => false]);

        $this->actingAs(groupAdmin())
            ->put(
                route('admin.business.groups.restore', $group)
            )
            ->assertRedirect();

        expect($group->fresh()->is_active)->toBeTrue();
    }
);

test(
    'a group cannot be placed inside itself',
    function () {
        seedGroups();

        $group = OrganisationGroup::firstOrFail();

        $this->actingAs(groupAdmin())
            ->put(
                route('admin.business.groups.update', $group),
                [
                    'name' => $group->name,
                    'group_type_id' => $group->group_type_id,
                    'primary_parent_id' => $group->id,
                ]
            )
            ->assertSessionHasErrors('primary_parent_id');
    }
);

test(
    'a group cannot be moved inside its own descendant',
    function () {
        seedGroups();

        $school = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'School')
            )
            ->firstOrFail();

        $programme = $school->children()->firstOrFail();

        /*
         * Allowing this would detach the whole branch from the
         * tree, leaving both groups unreachable.
         */
        $this->actingAs(groupAdmin())
            ->get(route('admin.business.groups.edit', $school))
            ->assertOk()
            ->assertDontSee($programme->name);
    }
);

test(
    'a lecturer cannot manage groups',
    function () {
        seedGroups();

        $lecturer = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $this->actingAs($lecturer)
            ->get(route('admin.business.groups.index', ['organisation' => OrganisationGroup::value('organisation_id')]))
            ->assertForbidden();
    }
);

test(
    'a group can sit in two branches at once',
    function () {
        seedGroups();

        $programme = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Programme')
            )
            ->firstOrFail();

        $intakeType = OrganisationGroupType::where(
            'name',
            'Intake Session'
        )->firstOrFail();

        $session = OrganisationGroup::create([
            'organisation_id' => $programme->organisation_id,
            'group_type_id' => $intakeType->id,
            'name' => 'January',
            'is_active' => true,
        ]);

        $class = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Class / Group')
            )
            ->firstOrFail();

        $class->parents()->syncWithoutDetaching([
            $session->id => ['is_primary' => false],
        ]);

        /*
         * One record, reachable from the programme branch and
         * from the intake branch. No duplicate row.
         */
        expect($class->parents()->count())->toBe(2);

        expect($class->primaryParent()->id)
            ->toBe($programme->id);
    }
);

test(
    'sponsorship scope reaches a student through every branch',
    function () {
        seedGroups();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $class = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'Class / Group')
            )
            ->firstOrFail();

        $intakeType = OrganisationGroupType::where(
            'name',
            'Intake'
        )->firstOrFail();

        $intake = OrganisationGroup::create([
            'organisation_id' => $class->organisation_id,
            'group_type_id' => $intakeType->id,
            'name' => 'Intake 14',
            'is_active' => true,
        ]);

        $class->parents()->syncWithoutDetaching([
            $intake->id => ['is_primary' => false],
        ]);

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        $groupIds = app(EntitlementService::class)
            ->scopeGroupIdsFor($student->fresh());

        /*
         * Sponsoring the intake must now reach this student,
         * just as sponsoring their school always did.
         */
        expect($groupIds)->toContain($intake->id);

        expect($groupIds)->toContain(
            $class->primaryParent()->id
        );
    }
);

test(
    'a group with children cannot be deleted',
    function () {
        seedGroups();

        $school = OrganisationGroup::query()
            ->whereHas(
                'type',
                fn ($q) => $q->where('name', 'School')
            )
            ->firstOrFail();

        $this->actingAs(groupAdmin())
            ->delete(
                route('admin.business.groups.destroy', $school)
            )
            ->assertSessionHasErrors('group');

        expect($school->fresh())->not->toBeNull();
    }
);

test(
    'an unused group can be deleted',
    function () {
        seedGroups();

        $spare = OrganisationGroup::create([
            'organisation_id' => OrganisationGroup::value(
                'organisation_id'
            ),
            'group_type_id' => OrganisationGroupType::value('id'),
            'name' => 'Temporary group',
            'is_active' => true,
        ]);

        $this->actingAs(groupAdmin())
            ->delete(
                route('admin.business.groups.destroy', $spare)
            )
            ->assertRedirect();

        expect($spare->fresh())->toBeNull();
    }
);

test(
    'a type in use cannot be removed',
    function () {
        seedGroups();

        $type = OrganisationGroupType::where(
            'name',
            'School'
        )->firstOrFail();

        $this->actingAs(groupAdmin())
            ->delete(
                route('admin.business.groups.types.destroy', $type)
            )
            ->assertSessionHasErrors('type');

        expect($type->fresh())->not->toBeNull();
    }
);

test(
    'an admin can name a new type',
    function () {
        seedGroups();

        $this->actingAs(groupAdmin())
            ->post(
                route('admin.business.groups.types.store'),
                [
                    'organisation_id' => OrganisationGroup::value(
                        'organisation_id'
                    ),
                    'name' => 'Faculty',
                ]
            )
            ->assertRedirect();

        /*
         * Nothing about the structure is fixed in code. An
         * institution names its own levels.
         */
        expect(
            OrganisationGroupType::where('name', 'Faculty')->exists()
        )->toBeTrue();
    }
);

test(
    'the tree shows a group in every branch it belongs to',
    function () {
        seedGroups();

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

        $this->actingAs(groupAdmin())
            ->get(route('admin.business.groups.index', ['organisation' => OrganisationGroup::value('organisation_id')]))
            ->assertOk()
            ->assertSee('also under');
    }
);
