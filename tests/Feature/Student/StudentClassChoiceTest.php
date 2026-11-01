<?php

use App\Models\LecturerAssignment;
use App\Models\OrganisationGroup;
use App\Models\OrganisationGroupType;
use App\Models\User;
use Database\Seeders\OrganisationGroupSeeder;

/*
 * Students choose their own class, from those under their
 * programme. It counts at once: the class's lecturer sees them,
 * and can take them out if they chose wrongly.
 */
const DADT_PROGRAMME = 'Diploma in ICT (Application Development)';
const DDAT_PROGRAMME = 'Diploma in ICT (Data Analytics)';

function classSetup(): array
{
    test()->seed(OrganisationGroupSeeder::class);

    $dadt04 = OrganisationGroup::where('code', 'DADT04')->sole();
    $ddat = OrganisationGroup::where('code', 'DDAT')->sole();

    $ddat01 = OrganisationGroup::create([
        'organisation_id' => $ddat->organisation_id,
        'group_type_id' => OrganisationGroupType::where('name', 'Class / Group')->sole()->id,
        'name' => 'DDAT01',
        'code' => 'DDAT01',
        'is_active' => true,
    ]);
    $ddat01->parents()->attach($ddat->id, ['is_primary' => true]);

    return [$dadt04, $ddat01];
}

function classStudent(): User
{
    return User::factory()->create([
        'role' => 'student',
        'name' => 'Nur Aisyah binti Hassan',
        'student_id' => '24FTT2803',
    ]);
}

function saveProfile(User $student, array $fields)
{
    return test()->actingAs($student)
        ->from(route('student.profile.edit'))
        ->put(route('student.profile.update'), array_merge([
            'name' => $student->name,
            'student_id' => '24FTT2803',
        ], $fields));
}

function isIn(User $student, OrganisationGroup $group): bool
{
    return $student->groupMemberships()->where('organisation_group_id', $group->id)->exists();
}

test('a student can put themselves in a class under their programme, and its lecturer sees them', function () {
    [$dadt04] = classSetup();
    $student = classStudent();

    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => $dadt04->id])
        ->assertSessionHasNoErrors();

    $programme = OrganisationGroup::where('name', DADT_PROGRAMME)->sole();

    expect(isIn($student, $dadt04))->toBeTrue()
        /* The class places them in the programme already. */
        ->and(isIn($student, $programme))->toBeFalse();

    $lecturer = User::factory()->create(['role' => 'lecturer']);
    LecturerAssignment::create(['user_id' => $lecturer->id, 'organisation_group_id' => $dadt04->id]);

    $this->actingAs($lecturer)
        ->get(route('lecturer.students.index'))
        ->assertOk()
        ->assertSee('Nur Aisyah binti Hassan')
        ->assertSee('Remove from DADT04');
});

test('a class from another programme cannot be chosen', function () {
    [, $ddat01] = classSetup();
    $student = classStudent();

    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => $ddat01->id])
        ->assertSessionHasErrors(['class_group_id' => 'Choose a class from your own programme.']);

    expect(isIn($student, $ddat01))->toBeFalse();
});

test('clearing the class puts them back in the programme alone', function () {
    [$dadt04] = classSetup();
    $student = classStudent();

    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => $dadt04->id]);
    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => '']);

    expect(isIn($student, $dadt04))->toBeFalse()
        ->and(isIn($student, OrganisationGroup::where('name', DADT_PROGRAMME)->sole()))->toBeTrue();
});

test('changing programme lets go of a class from the old one', function () {
    [$dadt04] = classSetup();
    $student = classStudent();

    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => $dadt04->id]);

    /* With no classes offered the list is switched off, so it is not sent. */
    saveProfile($student, ['programme' => DDAT_PROGRAMME])->assertSessionHasNoErrors();

    expect(isIn($student, $dadt04))->toBeFalse()
        ->and(isIn($student, OrganisationGroup::where('name', DDAT_PROGRAMME)->sole()))->toBeTrue();
});

test('Edit Profile offers each class with the programme it belongs to', function () {
    [$dadt04, $ddat01] = classSetup();
    $student = classStudent();

    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => $dadt04->id]);

    $this->actingAs($student->fresh())
        ->get(route('student.profile.edit'))
        ->assertOk()
        ->assertSee('name="class_group_id"', false)
        ->assertSee('data-programme="'.DADT_PROGRAMME.'"', false)
        ->assertSee('data-programme="'.DDAT_PROGRAMME.'"', false)
        ->assertSee('value="'.$dadt04->id.'"', false);
});

test('a lecturer can take a student out of a class they teach, and only that', function () {
    [$dadt04, $ddat01] = classSetup();
    $student = classStudent();

    saveProfile($student, ['programme' => DADT_PROGRAMME, 'class_group_id' => $dadt04->id]);

    $other = User::factory()->create(['role' => 'lecturer']);
    LecturerAssignment::create(['user_id' => $other->id, 'organisation_group_id' => $ddat01->id]);

    $this->actingAs($other)
        ->delete(route('lecturer.students.class.destroy', [$student, $dadt04]))
        ->assertNotFound();

    expect(isIn($student, $dadt04))->toBeTrue();

    $lecturer = User::factory()->create(['role' => 'lecturer']);
    LecturerAssignment::create(['user_id' => $lecturer->id, 'organisation_group_id' => $dadt04->id]);

    $this->actingAs($lecturer)
        ->delete(route('lecturer.students.class.destroy', [$student, $dadt04]))
        ->assertRedirect(route('lecturer.students.index'));

    expect(isIn($student, $dadt04))->toBeFalse()
        ->and(isIn($student, OrganisationGroup::where('name', DADT_PROGRAMME)->sole()))->toBeTrue();
});
