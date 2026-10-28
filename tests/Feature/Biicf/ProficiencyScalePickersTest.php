<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use Database\Seeders\BiicfReferenceDataSeeder;

/*
 * Everywhere a proficiency level is offered or accepted.
 *
 * Both scales share one table, so anything that reaches for
 * "all proficiency levels" now gets nine rows spanning two
 * ladders. Offering a soft-skill level for a technical
 * competency is not a slightly wrong answer, it is a
 * meaningless one, and these are the places it could happen.
 */

function technicalLevel(int $number): BiicfProficiencyLevel
{
    return BiicfProficiencyLevel::technical()
        ->where('level_number', $number)
        ->sole();
}

function softLevel(int $number): BiicfProficiencyLevel
{
    return BiicfProficiencyLevel::soft()
        ->where('level_number', $number)
        ->sole();
}

describe('the student explorer', function () {
    test('it presents the two scales separately', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get(route('student.biicf-explorer.index'))
            ->assertSuccessful()
            ->assertShows('Technical skills')
            ->assertShows('Soft skills')
            ->assertShows('Strategise')
            ->assertShows('Intermediate');
    });
});

describe('the admin job role form', function () {
    test('a technical competency is offered only technical levels', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        $response = $this->actingAs(
            User::factory()->create(['role' => 'admin'])
        )->get(route('admin.biicf.job-roles.create'));

        $response->assertSuccessful()
            ->assertShows('Level 5 - Strategise')
            ->assertHides('Level 4 - Expert');
    });

    test('a soft skill is offered only soft levels', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        BiicfCompetency::create([
            'name' => 'Decision-Making',
            'slug' => 'decision-making',
            'type' => 'soft_skill',
        ]);

        $response = $this->actingAs(
            User::factory()->create(['role' => 'admin'])
        )->get(route('admin.biicf.job-roles.create'));

        $response->assertSuccessful()
            ->assertShows('Level 4 - Expert')
            ->assertHides('Level 5 - Strategise');
    });

    test('saving a level from the wrong scale is refused', function () {
        /*
         * The form cannot produce this pairing, which is exactly
         * why it has to be checked on the way in rather than
         * only on the way out.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Cloud Computing',
            'slug' => 'cloud-computing',
            'type' => 'technical',
        ]);

        $subSector = BiicfSubSector::create([
            'name' => 'IT Services',
            'slug' => 'it-services',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.biicf.job-roles.store'), [
                'sub_sector_id' => $subSector->id,
                'title' => 'Systems Engineer',
                'competencies' => [
                    [
                        'id' => $competency->id,
                        'proficiency_level_id' => softLevel(2)->id,
                        'is_core' => 1,
                    ],
                ],
            ])
            ->assertSessionHasErrors('competencies');

        $this->assertDatabaseMissing('biicf_job_roles', [
            'title' => 'Systems Engineer',
        ]);
    });

    test('saving a level from the right scale is accepted', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Cloud Computing',
            'slug' => 'cloud-computing',
            'type' => 'technical',
        ]);

        $subSector = BiicfSubSector::create([
            'name' => 'IT Services',
            'slug' => 'it-services',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.biicf.job-roles.store'), [
                'sub_sector_id' => $subSector->id,
                'title' => 'Systems Engineer',
                'competencies' => [
                    [
                        'id' => $competency->id,
                        'proficiency_level_id' => technicalLevel(3)->id,
                        'is_core' => 1,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('biicf_job_role_competency', [
            'competency_id' => $competency->id,
            'proficiency_level_id' => technicalLevel(3)->id,
        ]);
    });
});

describe('the student profile form', function () {
    test('each competency is offered only its own scale', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        $response = $this->actingAs(
            User::factory()->create(['role' => 'student'])
        )->get(route('student.profile.edit'));

        $response->assertSuccessful()
            ->assertShows('Strategise');
    });

    test('a student cannot record a level from the wrong scale', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        /*
         * The profile form requires a Student ID, so it has to
         * be sent for the request to reach the competency
         * handling this test is actually about.
         */
        $student = User::factory()->create([
            'role' => 'student',
            'student_id' => '24FTT2803',
        ]);

        $this->actingAs($student)
            ->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), [
                'name' => $student->name,
                'student_id' => $student->student_id,
                'biicf_mode' => '1',
                'biicf_competencies' => [
                    $competency->id => [
                        'selected' => '1',
                        'proficiency_level_id' => softLevel(3)->id,
                    ],
                ],
            ])
            ->assertSessionHasErrors(
                "biicf_competencies.{$competency->id}.proficiency_level_id"
            );

        $this->assertDatabaseMissing('student_competencies', [
            'user_id' => $student->id,
        ]);
    });
});
