<?php

use App\Models\OrganisationGroup;
use App\Models\User;
use App\Services\Business\ProgrammeEnrolmentService;
use Database\Seeders\OrganisationGroupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedForRename(): void
{
    test()->seed(OrganisationGroupSeeder::class);
}

function renameEnrolment(): ProgrammeEnrolmentService
{
    return app(ProgrammeEnrolmentService::class);
}

test(
    'enrolling records the programme group, not only its name',
    function () {
        seedForRename();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $programme = OrganisationGroup::where(
            'name',
            'Diploma in ICT (Data Analytics)'
        )->firstOrFail();

        renameEnrolment()->syncFor($student, $programme->name);

        expect($student->fresh()->programme_group_id)
            ->toBe($programme->id);
    }
);

test(
    'renaming a programme does not detach its students',
    function () {
        seedForRename();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $programme = OrganisationGroup::where(
            'name',
            'Diploma in ICT (Data Analytics)'
        )->firstOrFail();

        renameEnrolment()->syncFor($student, $programme->name);

        $programme->update([
            'name' => 'Diploma in Data Analytics',
        ]);

        /*
         * The student's stored name is now out of date, which is
         * exactly the case that used to break. Saving the
         * profile again must keep them where they are rather
         * than treating them as belonging nowhere.
         */
        $student = $student->fresh();

        renameEnrolment()->syncFor($student, $student->programme);

        $student = $student->fresh();

        expect($student->programme_group_id)
            ->toBe($programme->id)
            ->and($student->programme)
            ->toBe('Diploma in Data Analytics');

        expect(
            $student->groupMemberships()
                ->where('organisation_group_id', $programme->id)
                ->exists()
        )->toBeTrue();
    }
);

test(
    'moving a programme to a different parent keeps its students',
    function () {
        seedForRename();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $programme = OrganisationGroup::where(
            'name',
            'Diploma in ICT (Cloud Networking)'
        )->firstOrFail();

        renameEnrolment()->syncFor($student, $programme->name);

        $school = OrganisationGroup::where('code', 'SICT')
            ->firstOrFail();

        $other = OrganisationGroup::create([
            'organisation_id' => $programme->organisation_id,
            'group_type_id' => $school->group_type_id,
            'name' => 'School of Business Studies',
            'is_active' => true,
        ]);

        $programme->parents()->sync([
            $other->id => ['is_primary' => true],
        ]);

        /*
         * Membership points at the group, so reorganising the
         * structure above it changes nothing for the student.
         */
        expect(
            $student->fresh()
                ->groupMemberships()
                ->where('organisation_group_id', $programme->id)
                ->exists()
        )->toBeTrue();
    }
);

test(
    'changing programme moves the recorded key as well',
    function () {
        seedForRename();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        renameEnrolment()->syncFor(
            $student,
            'Diploma in ICT (Data Analytics)'
        );

        renameEnrolment()->syncFor(
            $student->fresh(),
            'Diploma in ICT (Cloud Networking)'
        );

        $expected = OrganisationGroup::where(
            'name',
            'Diploma in ICT (Cloud Networking)'
        )->value('id');

        expect($student->fresh()->programme_group_id)
            ->toBe($expected);
    }
);

test(
    'clearing the programme clears the key',
    function () {
        seedForRename();

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        renameEnrolment()->syncFor(
            $student,
            'Diploma in ICT (Data Analytics)'
        );

        renameEnrolment()->syncFor($student->fresh(), null);

        expect($student->fresh()->programme_group_id)
            ->toBeNull();
    }
);
