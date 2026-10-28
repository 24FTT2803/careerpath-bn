<?php

use App\Models\BiicfCompetency;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * A role's competency table, which has a column per tier.
 *
 * The book prints one for each of a role's tiers, and the same
 * competency can be wanted at a different level in each —
 * Application Development at 1, 2 and 2 across IT Technician's
 * three levels. Both admin pages showed the entry tier alone:
 * the detail page said so in a note, and the form offered one
 * level, so the other columns could be read nowhere and edited
 * nowhere.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function roleWithThreeTiers(): BiicfJobRole
{
    return BiicfJobRole::where('title', 'IT Technician')->sole();
}

test('the detail page carries every tier, not just the first', function () {
    $role = roleWithThreeTiers();

    expect($role->tiers)->toHaveCount(3);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.biicf.job-roles.show', $role))
        ->assertOk();

    foreach ($role->tiers as $tier) {
        $response->assertSee($tier->label);
    }

    /*
     * The note that stood in for the other two columns.
     */
    $response->assertHides('This role has 3 tiers');
});

test('the form offers a level for each tier', function () {
    $role = roleWithThreeTiers();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.biicf.job-roles.edit', $role))
        ->assertOk();

    foreach ($role->tiers as $tier) {
        $response->assertSee("[levels][{$tier->id}]", false);
    }

    $response->assertShows('Not required');
});

test('a competency can be saved at a different level per tier', function () {
    $role = roleWithThreeTiers();

    $competency = BiicfCompetency::where('type', 'technical')->orderBy('name')->first();

    $wanted = $role->tiers->mapWithKeys(fn ($tier, $index) => [
        $tier->id => BiicfProficiencyLevel::technical()
            ->where('level_number', $index + 1)
            ->value('id'),
    ]);

    $this->actingAs($this->admin)
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
            'competencies' => [
                $competency->id => [
                    'id' => $competency->id,
                    'levels' => $wanted->all(),
                    'is_core' => 1,
                ],
            ],
        ])
        ->assertRedirect();

    foreach ($role->refresh()->tiers as $tier) {
        $held = $tier->competencies->firstWhere('id', $competency->id);

        expect($held)->not->toBeNull()
            ->and($held->pivot->proficiency_level_id)->toBe($wanted[$tier->id]);
    }
});

test('a tier left at not required gets no row', function () {
    /*
     * Which is what an empty cell in the book's table says: the
     * framework asks for nothing there, so storing a level would
     * invent a requirement.
     */
    $role = roleWithThreeTiers();
    $competency = BiicfCompetency::where('type', 'technical')->orderBy('name')->first();

    $first = $role->tiers->first();
    $last = $role->tiers->last();

    $this->actingAs($this->admin)
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
            'competencies' => [
                $competency->id => [
                    'id' => $competency->id,
                    'levels' => [
                        $first->id => '',
                        $last->id => BiicfProficiencyLevel::technical()->where('level_number', 4)->value('id'),
                    ],
                ],
            ],
        ])
        ->assertRedirect();

    $role->refresh();

    expect($role->tiers->firstWhere('id', $first->id)->competencies)->toHaveCount(0)
        ->and($role->tiers->firstWhere('id', $last->id)->competencies->pluck('id')->all())
        ->toBe([$competency->id]);
});

test('a save that says nothing about competencies leaves the whole table alone', function () {
    /*
     * The table is three columns of the book's own data. Reading
     * silence as an empty one would strip the role of every
     * proficiency it has.
     */
    $role = roleWithThreeTiers();

    $was = $role->tiers->map(fn ($tier) => $tier->competencies->count())->all();

    expect(array_sum($was))->toBeGreaterThan(0);

    $this->actingAs($this->admin)
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
        ])
        ->assertRedirect();

    expect($role->refresh()->tiers->map(fn ($tier) => $tier->competencies->count())->all())
        ->toBe($was);
});

test('a role created with one level still gets its proficiency', function () {
    /*
     * The create form has no tier columns to offer — the role's
     * single tier does not exist until it is saved — so it sends
     * one level, and that goes to the tier created with the role.
     */
    $competency = BiicfCompetency::where('type', 'soft_skill')->orderBy('name')->first();

    $level = BiicfProficiencyLevel::soft()->where('level_number', 2)->value('id');

    $this->actingAs($this->admin)
        ->post(route('admin.biicf.job-roles.store'), [
            'sub_sector_id' => BiicfJobRole::first()->sub_sector_id,
            'title' => 'Service Desk Analyst',
            'competencies' => [
                $competency->id => [
                    'id' => $competency->id,
                    'proficiency_level_id' => $level,
                ],
            ],
        ])
        ->assertRedirect();

    $tier = BiicfJobRole::where('title', 'Service Desk Analyst')->sole()->tiers()->sole();

    expect($tier->competencies->pluck('id')->all())->toBe([$competency->id])
        ->and($tier->competencies->first()->pivot->proficiency_level_id)->toBe($level);
});

test('a competency ticked at no level at all is refused, not silently dropped', function () {
    /*
     * Ticking a competency and choosing nothing is a half-filled
     * form, not a statement that the role needs it nowhere.
     */
    $role = roleWithThreeTiers();
    $competency = BiicfCompetency::where('type', 'technical')->orderBy('name')->first();

    $this->actingAs($this->admin)
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
            'competencies' => [
                $competency->id => [
                    'id' => $competency->id,
                    'levels' => $role->tiers->mapWithKeys(fn ($tier) => [$tier->id => ''])->all(),
                ],
            ],
        ])
        ->assertSessionHasErrors('competencies');
});

test('a soft skill cannot be given a technical rung', function () {
    /*
     * The two scales number their rungs separately, so a soft
     * skill handed a technical level would read as a rung it does
     * not have.
     */
    $role = roleWithThreeTiers();
    $competency = BiicfCompetency::where('type', 'soft_skill')->orderBy('name')->first();

    $this->actingAs($this->admin)
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
            'competencies' => [
                $competency->id => [
                    'id' => $competency->id,
                    'levels' => [
                        $role->tiers->first()->id => BiicfProficiencyLevel::technical()
                            ->where('level_number', 5)->value('id'),
                    ],
                ],
            ],
        ])
        ->assertSessionHasErrors('competencies');
});
