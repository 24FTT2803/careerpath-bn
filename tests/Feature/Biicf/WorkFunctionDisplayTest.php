<?php

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobDescriptionPoint;
use App\Models\BiicfJobFamily;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\Biicf\JobRoleContentUpdater;
use Illuminate\Support\Str;

/*
 * The pages that show a role's duties, now reading rows rather
 * than newline-separated text.
 *
 * The form stays a textarea, so what matters is that typing in
 * it produces the rows the pages read, that editing it leaves
 * the book's family-specific duties alone, and that both ends
 * agree — a display reading rows while the form wrote text
 * would look like saving had silently failed.
 */

function displayRole(string $title = 'IT Technician'): BiicfJobRole
{
    $subSector = BiicfSubSector::firstOrCreate(
        ['slug' => 'it-services'],
        ['name' => 'IT Services']
    );

    return BiicfJobRole::create([
        'sub_sector_id' => $subSector->id,
        'title' => $title,
        'slug' => Str::slug($title),
    ]);
}

describe('writing from the form', function () {
    test('each line becomes a point', function () {
        $role = displayRole();

        (new JobRoleContentUpdater)->replaceDescription(
            $role,
            "Provide support functions.\n\nMaintain IT equipment.\n"
        );

        expect($role->descriptionPoints()->pluck('text')->all())->toBe([
            'Provide support functions.',
            'Maintain IT equipment.',
        ]);
    });

    test('each line becomes a generic duty', function () {
        $role = displayRole();

        (new JobRoleContentUpdater)->replaceGenericWorkFunctions(
            $role,
            "Install desktop hardware.\nRecord keeping of incidents."
        );

        expect($role->genericWorkFunctions()->pluck('text')->all())->toBe([
            'Install desktop hardware.',
            'Record keeping of incidents.',
        ]);
    });

    test('rewriting the form leaves family duties alone', function () {
        /*
         * Family-specific duties come from the reference book and
         * cannot be typed here, so the textarea must not be able
         * to delete them.
         */
        $role = displayRole();
        $family = BiicfJobFamily::create([
            'name' => 'Server and Network Support',
            'slug' => 'server-and-network-support',
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'job_family_id' => $family->id,
            'text' => 'Install and maintain standard network cabling.',
        ]);

        $updater = new JobRoleContentUpdater;
        $updater->replaceGenericWorkFunctions($role, 'Install desktop hardware.');
        $updater->replaceGenericWorkFunctions($role, 'Something else entirely.');

        expect($role->genericWorkFunctions()->count())->toBe(1)
            ->and($role->specificWorkFunctions()->count())->toBe(1);
    });

    test('clearing the field removes the generic duties', function () {
        $role = displayRole();

        $updater = new JobRoleContentUpdater;
        $updater->replaceGenericWorkFunctions($role, 'Install desktop hardware.');
        $updater->replaceGenericWorkFunctions($role, '');

        expect($role->genericWorkFunctions()->count())->toBe(0);
    });
});

describe('the admin form and show page agree', function () {
    test('what is typed in is what is shown', function () {
        /*
         * The round trip that matters: a form that writes rows
         * and a page that reads them.
         */
        $subSector = BiicfSubSector::create([
            'name' => 'IT Services',
            'slug' => 'it-services',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.biicf.job-roles.store'), [
                'sub_sector_id' => $subSector->id,
                'title' => 'Systems Engineer',
                'job_description' => "Designs systems.\nSupports deployments.",
                'critical_work_function' => "Plan capacity.\nPatch servers.",
            ])
            ->assertSessionHasNoErrors();

        $role = BiicfJobRole::where('title', 'Systems Engineer')->sole();

        $this->actingAs($admin)
            ->get(route('admin.biicf.job-roles.show', $role))
            ->assertSuccessful()
            ->assertShows('Designs systems.')
            ->assertShows('Supports deployments.')
            ->assertShows('Plan capacity.')
            ->assertShows('Patch servers.');
    });

    test('editing replaces what the page shows', function () {
        $subSector = BiicfSubSector::create([
            'name' => 'IT Services',
            'slug' => 'it-services',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $role = displayRole('Network Engineer');

        $this->actingAs($admin)
            ->put(route('admin.biicf.job-roles.update', $role), [
                'sub_sector_id' => $subSector->id,
                'title' => 'Network Engineer',
                'job_description' => 'Runs the network.',
                'critical_work_function' => 'Configure routers.',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->get(route('admin.biicf.job-roles.show', $role))
            ->assertSuccessful()
            ->assertShows('Runs the network.')
            ->assertShows('Configure routers.');
    });
});

describe('the explorer endpoint', function () {
    test('it ships the duties as structure', function () {
        $role = displayRole();
        $family = BiicfJobFamily::create([
            'name' => 'Desktop and Application Support',
            'slug' => 'desktop-and-application-support',
        ]);

        BiicfJobDescriptionPoint::create([
            'job_role_id' => $role->id,
            'text' => 'Provide support functions.',
            'sort_order' => 1,
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'text' => 'Install desktop hardware.',
        ]);

        $heading = BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'job_family_id' => $family->id,
            'text' => 'Configuration and Installation',
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'job_family_id' => $family->id,
            'parent_id' => $heading->id,
            'text' => 'Update records of installed hardware.',
        ]);

        $response = $this->actingAs(
            User::factory()->create(['role' => 'student'])
        )->getJson(
            route('student.biicf-explorer.job-role.show', $role)
        );

        $response->assertSuccessful()
            ->assertJsonPath(
                'job_role.description_points.0.text',
                'Provide support functions.'
            )
            ->assertJsonPath(
                'job_role.generic_work_functions.0.text',
                'Install desktop hardware.'
            )
            ->assertJsonPath(
                'specific_work_functions_by_family.0.family',
                'Desktop and Application Support'
            )
            ->assertJsonPath(
                'specific_work_functions_by_family.0.functions.0.children.0.text',
                'Update records of installed hardware.'
            );
    });

    test('a nested duty is not also listed on its own', function () {
        /*
         * Sub-duties are reached through their parent. Listing
         * them alongside it would show each one twice.
         */
        $role = displayRole();

        $heading = BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'text' => 'Configuration and Installation',
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'parent_id' => $heading->id,
            'text' => 'Update records of installed hardware.',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->getJson(route('student.biicf-explorer.job-role.show', $role))
            ->assertSuccessful()
            ->assertJsonCount(1, 'job_role.generic_work_functions')
            ->assertJsonPath(
                'job_role.generic_work_functions.0.children.0.text',
                'Update records of installed hardware.'
            );
    });
});
