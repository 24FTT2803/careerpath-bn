<?php

use App\Models\OrganisationGroup;
use App\Models\User;
use App\Services\Business\EntitlementService;
use App\Services\Business\ProgrammeEnrolmentService;
use Database\Seeders\FeatureDefinitionSeeder;
use Database\Seeders\OrganisationGroupSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedForEnrolment(): void
{
    test()->seed(OrganisationGroupSeeder::class);
}

function enrolment(): ProgrammeEnrolmentService
{
    return app(ProgrammeEnrolmentService::class);
}

function programmeNamed(string $name): OrganisationGroup
{
    return OrganisationGroup::where('name', $name)->firstOrFail();
}

test(
    'programmes are read from the structure',
    function () {
        seedForEnrolment();

        $names = enrolment()->options()->pluck('name')->all();

        /*
         * The list used to be written into three controllers and
         * four templates. Adding a programme to the tree is now
         * all it takes.
         */
        expect($names)
            ->toContain('Diploma in ICT (Application Development)')
            ->toContain('Diploma in ICT (Data Analytics)')
            ->toContain('Diploma in ICT (Cloud Networking)');
    }
);

test(
    'choosing a programme puts the student in that group',
    function () {
        seedForEnrolment();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $programme = programmeNamed(
            'Diploma in ICT (Data Analytics)'
        );

        enrolment()->syncFor($student, $programme->name);

        expect(
            $student->groupMemberships()
                ->where('organisation_group_id', $programme->id)
                ->exists()
        )->toBeTrue();
    }
);

test(
    'a student already in a class gains no second membership',
    function () {
        seedForEnrolment();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $class = OrganisationGroup::where('name', 'DADT04')
            ->firstOrFail();

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        enrolment()->syncFor(
            $student,
            'Diploma in ICT (Application Development)'
        );

        /*
         * The class already sits under the programme, so a
         * second membership would duplicate that and linger if
         * the student later moved class.
         */
        expect($student->groupMemberships()->count())->toBe(1);
    }
);

test(
    'changing programme moves the student',
    function () {
        seedForEnrolment();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        enrolment()->syncFor(
            $student,
            'Diploma in ICT (Data Analytics)'
        );

        enrolment()->syncFor(
            $student->fresh(),
            'Diploma in ICT (Cloud Networking)'
        );

        $ids = $student->groupMemberships()
            ->pluck('organisation_group_id')
            ->all();

        expect($ids)->toBe([
            programmeNamed(
                'Diploma in ICT (Cloud Networking)'
            )->id,
        ]);
    }
);

test(
    'clearing the programme removes the membership',
    function () {
        seedForEnrolment();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        enrolment()->syncFor(
            $student,
            'Diploma in ICT (Data Analytics)'
        );

        enrolment()->syncFor($student->fresh(), null);

        expect($student->groupMemberships()->count())->toBe(0);
    }
);

test(
    'a class membership survives a programme change',
    function () {
        seedForEnrolment();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $class = OrganisationGroup::where('name', 'DADT04')
            ->firstOrFail();

        $student->groupMemberships()->create([
            'organisation_group_id' => $class->id,
        ]);

        enrolment()->syncFor(
            $student->fresh(),
            'Diploma in ICT (Data Analytics)'
        );

        /*
         * A class is assigned deliberately and carries the
         * student's intake as well as their programme, so it is
         * never removed by a programme change.
         */
        expect(
            $student->groupMemberships()
                ->where('organisation_group_id', $class->id)
                ->exists()
        )->toBeTrue();
    }
);

test(
    'sponsoring the school now reaches a student who only chose a programme',
    function () {
        seedForEnrolment();
        test()->seed(FeatureDefinitionSeeder::class);
        test()->seed(PlanSeeder::class);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        enrolment()->syncFor(
            $student,
            'Diploma in ICT (Cloud Networking)'
        );

        $school = OrganisationGroup::where('code', 'SICT')
            ->firstOrFail();

        $scope = app(EntitlementService::class)
            ->scopeGroupIdsFor($student->fresh());

        /*
         * The whole point: before this, a programme was a string
         * matching nothing, so sponsoring SICT reached nobody.
         */
        expect($scope)->toContain($school->id);
    }
);
