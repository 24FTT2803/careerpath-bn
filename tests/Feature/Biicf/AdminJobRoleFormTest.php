<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\AI\CareerAdviserContextBuilder;
use App\Services\Biicf\JobRoleContentUpdater;
use Database\Seeders\BiicfReferenceDataSeeder;

/*
 * The admin job role form, now that a role's content lives in
 * rows and its proficiencies belong to a tier.
 *
 * These cover the consumers that were left reading the old
 * shape. Each would have looked fine today and failed quietly
 * once the reference book was imported — an empty textarea that
 * wipes a role's duties on save, a competency saved against no
 * tier and so displayed nowhere, an adviser with no
 * descriptions.
 */

function formSubSector(): BiicfSubSector
{
    return BiicfSubSector::firstOrCreate(
        ['slug' => 'it-services'],
        ['name' => 'IT Services']
    );
}

function formAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

describe('saving competencies', function () {
    test('they attach to the role\'s tier', function () {
        /*
         * Attaching through the role leaves the tier null, and a
         * row with no tier appears on no page at all.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $competency = BiicfCompetency::create([
            'name' => 'Cloud Computing',
            'slug' => 'cloud-computing',
            'type' => 'technical',
        ]);

        $this->actingAs(formAdmin())
            ->post(route('admin.biicf.job-roles.store'), [
                'sub_sector_id' => formSubSector()->id,
                'title' => 'Systems Engineer',
                'competencies' => [
                    [
                        'id' => $competency->id,
                        'proficiency_level_id' => BiicfProficiencyLevel::technical()
                            ->where('level_number', 3)
                            ->sole()
                            ->id,
                        'is_core' => 1,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $role = BiicfJobRole::where('title', 'Systems Engineer')->sole();

        $this->assertDatabaseMissing('biicf_job_role_competency', [
            'job_role_id' => $role->id,
            'job_role_tier_id' => null,
        ]);

        expect($role->entryTier->competencies)->toHaveCount(1);
    });

    test('editing one tier leaves the others alone', function () {
        /*
         * A blanket detach on the role would take every column of
         * the book's table with it, not just the one being
         * edited.
         */
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = BiicfJobRole::create([
            'sub_sector_id' => formSubSector()->id,
            'title' => 'IT Technician',
            'slug' => 'it-technician',
        ]);

        $competency = BiicfCompetency::create([
            'name' => 'Application Development',
            'slug' => 'application-development',
            'type' => 'technical',
        ]);

        $level = BiicfProficiencyLevel::technical()
            ->where('level_number', 2)
            ->sole();

        foreach ([1, 2] as $number) {
            BiicfJobRoleTier::create([
                'job_role_id' => $role->id,
                'label' => "Level {$number}",
                'sort_order' => $number,
            ])->competencies()->attach($competency->id, [
                'proficiency_level_id' => $level->id,
                'is_core' => true,
            ]);
        }

        $this->actingAs(formAdmin())
            ->put(route('admin.biicf.job-roles.update', $role), [
                'sub_sector_id' => $role->sub_sector_id,
                'title' => 'IT Technician',
                'competencies' => [],
            ])
            ->assertSessionHasNoErrors();

        $tiers = $role->fresh()->tiers;

        expect($tiers->first()->competencies)->toHaveCount(0)
            ->and($tiers->last()->competencies)->toHaveCount(1);
    });
});

describe('the edit form', function () {
    test('it prefills from the stored rows', function () {
        /*
         * Reading the old text columns would show an empty box
         * for a role whose content came from the reference book,
         * and saving that box would delete its duties.
         */
        $role = BiicfJobRole::create([
            'sub_sector_id' => formSubSector()->id,
            'title' => 'Network Engineer',
            'slug' => 'network-engineer',
        ]);

        $updater = new JobRoleContentUpdater;
        $updater->replaceDescription($role, "Runs the network.\nKeeps it running.");
        $updater->replaceGenericWorkFunctions($role, 'Configure routers.');

        /*
         * Emptied on purpose: the rows are the truth, and the
         * form must not depend on the columns still agreeing.
         */
        $role->forceFill([
            'job_description' => null,
            'critical_work_function' => null,
        ])->save();

        $this->actingAs(formAdmin())
            ->get(route('admin.biicf.job-roles.edit', $role))
            ->assertSuccessful()
            ->assertSee('Runs the network.')
            ->assertSee('Keeps it running.')
            ->assertSee('Configure routers.');
    });
});

describe('the career adviser context', function () {
    test('role descriptions come from the stored rows', function () {
        $this->seed(BiicfReferenceDataSeeder::class);

        $role = BiicfJobRole::create([
            'sub_sector_id' => formSubSector()->id,
            'title' => 'Data Analyst',
            'slug' => 'data-analyst',
        ]);

        (new JobRoleContentUpdater)->replaceDescription(
            $role,
            'Interprets data to identify trends.'
        );

        $role->forceFill(['job_description' => null])->save();

        $context = app(CareerAdviserContextBuilder::class)->build(
            User::factory()->create(['role' => 'student'])
        );

        $described = collect($context['biicf_reference']['job_roles'])
            ->firstWhere('title', 'Data Analyst');

        expect($described['job_description'])
            ->toBe('Interprets data to identify trends.');
    });
});
