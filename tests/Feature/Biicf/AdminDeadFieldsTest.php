<?php

use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;
use Illuminate\Support\Facades\Schema;

/*
 * Fields the admin pages offered and the framework never fills.
 *
 * A training's provider and certification body and a
 * sub-sector's lead organisation were guesses at what the book
 * might say; it says none of them, so every row was null and
 * three columns of dashes sat in the tables. Box colour was for
 * a career-path diagram that was never built, and the detail
 * page printed it as white text on a colour name no browser
 * knows — invisible.
 */

function deadFieldsAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

test('the columns nothing ever wrote are gone from the schema', function () {
    expect(Schema::hasColumn('biicf_trainings', 'provider'))->toBeFalse()
        ->and(Schema::hasColumn('biicf_trainings', 'certification_body'))->toBeFalse()
        ->and(Schema::hasColumn('biicf_sub_sectors', 'lead_organisation'))->toBeFalse();
});

test('a training can still be created and searched without them', function () {
    $this->actingAs(deadFieldsAdmin())
        ->post(route('admin.biicf.trainings.store'), ['name' => 'CCNA Certification'])
        ->assertRedirect();

    $this->actingAs(deadFieldsAdmin())
        ->get(route('admin.biicf.trainings', ['search' => 'CCNA']))
        ->assertOk()
        ->assertSee('CCNA Certification');

    expect(BiicfTraining::where('name', 'CCNA Certification')->exists())->toBeTrue();
});

test('a sub-sector can still be created without a lead organisation', function () {
    $this->actingAs(deadFieldsAdmin())
        ->post(route('admin.biicf.sub-sectors.store'), [
            'name' => 'Emerging Technology',
            'sort_order' => 9,
        ])
        ->assertRedirect();

    expect(BiicfSubSector::where('name', 'Emerging Technology')->exists())->toBeTrue();
});

test('the role detail page no longer shows a colour nobody can see', function () {
    $this->seed(BiicfJobRoleSeeder::class);

    $this->actingAs(deadFieldsAdmin())
        ->get(route('admin.biicf.job-roles.show', BiicfJobRole::where('title', 'Data Entry')->sole()))
        ->assertOk()
        ->assertDontSee('Box Colour');
});

test('saving a role without the box colour field keeps the one it has', function () {
    /*
     * The column stays until the career path map that wanted it
     * exists or is given up on. What must not happen is the form
     * quietly rewriting it to a default on every save.
     */
    $this->seed(BiicfJobRoleSeeder::class);

    $role = BiicfJobRole::where('title', 'Data Entry')->sole();
    $role->update(['box_colour' => 'light-blue']);

    $this->actingAs(deadFieldsAdmin())
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
        ])
        ->assertRedirect();

    expect($role->refresh()->box_colour)->toBe('light-blue');
});

test('an entry requirement with no level stated says nothing instead', function () {
    /*
     * Project Coordinator is the role to ask: one of its ways in
     * is "any relevant Project Management Certification with
     * experience", which names no qualification level at all. The
     * sentence itself is shown underneath, so the badge was
     * standing in for something already said.
     */
    $this->seed(BiicfJobRoleSeeder::class);

    $role = BiicfJobRole::where('title', 'Project Coordinator')->sole();

    $this->actingAs(deadFieldsAdmin())
        ->get(route('admin.biicf.job-roles.show', $role))
        ->assertOk()
        ->assertDontSee('No level stated')
        ->assertSee('Project Management Certification', false);
});
