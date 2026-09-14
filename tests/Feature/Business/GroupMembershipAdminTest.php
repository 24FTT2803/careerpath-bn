<?php

use App\Models\OrganisationGroup;
use App\Models\User;
use App\Services\Business\ProgrammeEnrolmentService;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedForMembers(): void
{
    test()->seed(OrganisationGroupSeeder::class);
}

function membershipAdmin(): User
{
    return User::factory()->create([
        'role' => 'admin',
    ]);
}

function classGroup(): OrganisationGroup
{
    return OrganisationGroup::where('name', 'DADT04')
        ->firstOrFail();
}

test(
    'an admin can add a student to a class',
    function () {
        seedForMembers();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $class = classGroup();

        $this->actingAs(membershipAdmin())
            ->post(
                route(
                    'admin.business.groups.members.store',
                    $class
                ),
                ['user_ids' => [$student->id]]
            )
            ->assertRedirect();

        expect(
            $student->groupMemberships()
                ->where('organisation_group_id', $class->id)
                ->exists()
        )->toBeTrue();
    }
);

test(
    'an admin can remove a student from a class',
    function () {
        seedForMembers();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $class = classGroup();

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        $this->actingAs(membershipAdmin())
            ->delete(
                route(
                    'admin.business.groups.members.destroy',
                    [$class, $student]
                )
            )
            ->assertRedirect();

        expect($student->groupMemberships()->count())->toBe(0);
    }
);

test(
    'students are only offered when searched for',
    function () {
        seedForMembers();

        User::factory()->create([
            'role' => 'student',
            'name' => 'Findable Student',
        ]);

        $class = classGroup();

        /*
         * An institution has more students than fit on a page,
         * and adding the wrong one from a long list of similar
         * names is easy.
         */
        $this->actingAs(membershipAdmin())
            ->get(
                route(
                    'admin.business.groups.members.index',
                    $class
                )
            )
            ->assertOk()
            ->assertDontSee('Findable Student');

        $this->actingAs(membershipAdmin())
            ->get(
                route(
                    'admin.business.groups.members.index',
                    [$class, 'q' => 'Findable']
                )
            )
            ->assertOk()
            ->assertSee('Findable Student');
    }
);

test(
    'a student whose programme disagrees with the branch is flagged',
    function () {
        seedForMembers();

        $student = User::factory()->create([
            'role' => 'student',
            'programme' => 'Diploma in ICT (Data Analytics)',
        ]);

        $class = classGroup();

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        /*
         * DADT04 sits under Application Development, so this
         * student's own profile disagrees with the class they
         * are in. Surfaced rather than prevented.
         */
        $this->actingAs(membershipAdmin())
            ->get(
                route(
                    'admin.business.groups.members.index',
                    $class
                )
            )
            ->assertOk()
            ->assertSee('does not match this branch');
    }
);

test(
    'a matching programme is not flagged',
    function () {
        seedForMembers();

        $student = User::factory()->create([
            'role' => 'student',
            'programme' => 'Diploma in ICT (Application Development)',
        ]);

        $class = classGroup();

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        $this->actingAs(membershipAdmin())
            ->get(
                route(
                    'admin.business.groups.members.index',
                    $class
                )
            )
            ->assertOk()
            ->assertDontSee('does not match this branch');
    }
);

test(
    'a programme group warns that membership follows the profile',
    function () {
        seedForMembers();

        $programme = app(ProgrammeEnrolmentService::class)
            ->options()
            ->firstWhere(
                'name',
                'Diploma in ICT (Application Development)'
            );

        $this->actingAs(membershipAdmin())
            ->get(
                route(
                    'admin.business.groups.members.index',
                    $programme
                )
            )
            ->assertOk()
            ->assertSee('follows the student');
    }
);

test(
    'a lecturer cannot manage group membership',
    function () {
        seedForMembers();

        $lecturer = User::factory()->create([
            'role' => 'lecturer',
        ]);

        $this->actingAs($lecturer)
            ->get(
                route(
                    'admin.business.groups.members.index',
                    classGroup()
                )
            )
            ->assertForbidden();
    }
);
