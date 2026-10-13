<?php

use App\Models\BiicfJobRole;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * The four admin listings.
 *
 * They were paged on the server, so every page of results was a
 * round trip that reloaded the screen and dropped whatever
 * sorting and filtering had been set. None of the tables is
 * large, so each is now sent whole and sorts, filters and pages
 * itself — which is why these tests check that every row reaches
 * the page rather than the first fifteen.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('the job role listing sends every role, not the first page of them', function () {
    $roles = BiicfJobRole::orderBy('title')->get();

    expect($roles->count())->toBeGreaterThan(15);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.biicf.job-roles'))
        ->assertOk();

    /*
     * Including one that server paging would have put on the
     * third page.
     */
    $response->assertSee($roles->first()->title)
        ->assertSee($roles->last()->title);
});

test('the job level filter offers the framework\'s own levels', function () {
    /*
     * The Level column used to show career_path_level — a number
     * of this application's own invention. What an administrator
     * looks for is the framework's level, which is what the
     * buttons now offer.
     */
    $this->actingAs($this->admin)
        ->get(route('admin.biicf.job-roles'))
        ->assertOk()
        ->assertSee('All levels')
        ->assertSee('Entrant')
        ->assertSee('C-Suite')
        ->assertDontSee('Level 0');
});

/**
 * The rows a listing handed its table, read back out of the page.
 *
 * @return array<int, array<string, mixed>>
 */
function listingRows(string $html): array
{
    preg_match('/adminTable\((\[.*?\]),\s*\{/s', $html, $found);

    return json_decode(
        html_entity_decode($found[1] ?? '[]', ENT_QUOTES),
        true
    ) ?? [];
}

test('a role carrying two job levels says both', function () {
    /*
     * IT Administrator/Manager is entered at Entrant and held at
     * Expert/Management, so a single-valued level column could
     * never have shown it properly.
     */
    $content = $this->actingAs($this->admin)
        ->get(route('admin.biicf.job-roles'))
        ->assertOk()
        ->getContent();

    $role = collect(listingRows($content))
        ->firstWhere('title', 'IT Administrator/Manager');

    expect($role)->not->toBeNull()
        ->and($role['levels'])->toContain('Entrant')
        ->and($role['levels'])->toContain('Expert/Management');
});

test('the rows carry what the table sorts and filters on', function () {
    $content = $this->actingAs($this->admin)
        ->get(route('admin.biicf.job-roles'))
        ->assertOk()
        ->getContent();

    $rows = listingRows($content);

    expect($rows)->toHaveCount(BiicfJobRole::count())
        ->and($rows[0])->toHaveKeys([
            'id', 'title', 'sub_sector', 'sub_sector_id', 'levels',
            'functional_group', 'show_url', 'edit_url', 'destroy_url',
        ]);
});

test('every listing renders and carries its rows', function (string $route, string $needle) {
    $this->actingAs($this->admin)
        ->get(route($route))
        ->assertOk()
        ->assertSee('adminTable(', false)
        ->assertSee($needle);
})->with([
    ['admin.biicf.job-roles', 'Job Roles'],
    ['admin.biicf.competencies', 'Competencies'],
    ['admin.biicf.trainings', 'Trainings'],
    ['admin.biicf.sub-sectors', 'Sub-Sectors'],
]);

test('the competency listing counts how many levels each has described', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.biicf.competencies'))
        ->assertOk()
        ->assertSee('Levels described');
});

test('no listing asks the browser to load another page of results', function (string $route) {
    /*
     * The paginator's own links were the reload. If one comes
     * back, so does the complaint.
     */
    $content = $this->actingAs($this->admin)->get(route($route))->getContent();

    expect($content)->not->toContain('?page=');
})->with([
    ['admin.biicf.job-roles'],
    ['admin.biicf.competencies'],
    ['admin.biicf.trainings'],
    ['admin.biicf.sub-sectors'],
]);
