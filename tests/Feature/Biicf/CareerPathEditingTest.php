<?php

use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use App\Models\User;
use Database\Seeders\BiicfReferenceDataSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * Career path progression, editable by hand.
 *
 * biicf_career_path_edges was only ever written by the seeders.
 * BiicfJobRoleSeeder repoints edges when it dedupes roles but
 * creates none, the edition pipeline never touches the table and
 * reads no progression out of the book, and the admin job role
 * form offered career_path_level — a number — and nothing else.
 *
 * So a role added or corrected by hand had no progression and no
 * screen could give it one: the Explorer's Career Paths panel
 * showed "No career path data" for it permanently. Both
 * directions are editable, because both are edges touching the
 * role being edited.
 */

beforeEach(function () {
    $this->seed(BiicfReferenceDataSeeder::class);

    $this->admin = User::factory()->create(['role' => 'admin']);
});

function pathSubSector(): BiicfSubSector
{
    return BiicfSubSector::firstOrCreate(
        ['slug' => 'software-development'],
        ['name' => 'Software Development']
    );
}

function pathRole(string $title, int $level = 0): BiicfJobRole
{
    return BiicfJobRole::create([
        'sub_sector_id' => pathSubSector()->id,
        'title' => $title,
        'slug' => Str::slug($title),
        'career_path_level' => $level,
    ]);
}

/**
 * The fields the form always posts, so a test can send only the
 * progression it cares about.
 *
 * @return array<string, mixed>
 */
function rolePayload(array $overrides = []): array
{
    return array_merge([
        'sub_sector_id' => pathSubSector()->id,
        'title' => 'Application Developer',
        'career_path_level' => 0,
        'career_path_submitted' => 1,
    ], $overrides);
}

describe('setting a progression', function () {
    test('a role added by hand can be given a career path', function () {
        $junior = pathRole('Junior Developer');
        $senior = pathRole('Senior Developer', 2);

        $this->actingAs($this->admin)
            ->post(route('admin.biicf.job-roles.store'), rolePayload([
                'progresses_from' => [$junior->id],
                'progresses_to' => [$senior->id],
            ]))
            ->assertRedirect(route('admin.biicf.job-roles'));

        $created = BiicfJobRole::where('title', 'Application Developer')
            ->sole();

        expect($created->progressesFrom->pluck('title')->all())
            ->toBe(['Junior Developer'])
            ->and($created->progressesTo->pluck('title')->all())
            ->toBe(['Senior Developer']);
    });

    test('an existing role can be connected from the edit form', function () {
        $role = pathRole('Application Developer');
        $senior = pathRole('Senior Developer', 2);

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload(['progresses_to' => [$senior->id]])
            )
            ->assertRedirect(route('admin.biicf.job-roles'));

        expect($role->fresh()->progressesTo->pluck('id')->all())
            ->toBe([$senior->id]);
    });

    test('a role can lead to more than one', function () {
        /*
         * The table allows it and the framework uses it: one role
         * can open onto several.
         */
        $role = pathRole('Application Developer');
        $architect = pathRole('Solution Architect', 3);
        $lead = pathRole('Development Lead', 2);

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload([
                    'progresses_to' => [$architect->id, $lead->id],
                ])
            );

        expect($role->fresh()->progressesTo->pluck('id')->sort()->values()->all())
            ->toBe(collect([$architect->id, $lead->id])->sort()->values()->all());
    });
});

describe('removing one', function () {
    test('unticking everything clears the progression', function () {
        /*
         * No field is posted at all when every box is cleared, so
         * this is the case that would silently leave the old
         * edges in place.
         */
        $role = pathRole('Application Developer');
        $senior = pathRole('Senior Developer', 2);

        $role->progressesTo()->attach($senior->id);

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload()
            );

        expect($role->fresh()->progressesTo)->toHaveCount(0);
    });

    test('an edge removed one way does not take the other with it', function () {
        $role = pathRole('Application Developer');
        $junior = pathRole('Junior Developer');
        $senior = pathRole('Senior Developer', 2);

        $role->progressesFrom()->attach($junior->id);
        $role->progressesTo()->attach($senior->id);

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload(['progresses_from' => [$junior->id]])
            );

        expect($role->fresh()->progressesFrom->pluck('id')->all())
            ->toBe([$junior->id])
            ->and($role->fresh()->progressesTo)
            ->toHaveCount(0);
    });
});

describe('what it refuses', function () {
    test('a role cannot progress into itself', function () {
        /*
         * The unique key is on the pair, so it would not catch
         * this. The Explorer would draw the role as its own next
         * step.
         */
        $role = pathRole('Application Developer');

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload([
                    'progresses_to' => [$role->id],
                    'progresses_from' => [$role->id],
                ])
            );

        expect($role->fresh()->progressesTo)->toHaveCount(0)
            ->and($role->fresh()->progressesFrom)->toHaveCount(0);
    });

    test('a role that does not exist is refused', function () {
        $role = pathRole('Application Developer');

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload(['progresses_to' => [9999]])
            )
            ->assertSessionHasErrors('progresses_to.0');

        expect($role->fresh()->progressesTo)->toHaveCount(0);
    });

    test('saving the same progression twice does not duplicate it', function () {
        $role = pathRole('Application Developer');
        $senior = pathRole('Senior Developer', 2);

        foreach (range(1, 2) as $save) {
            $this->actingAs($this->admin)
                ->put(
                    route('admin.biicf.job-roles.update', $role),
                    rolePayload(['progresses_to' => [$senior->id]])
                );
        }

        expect(
            DB::table('biicf_career_path_edges')
                ->where('from_job_role_id', $role->id)
                ->where('to_job_role_id', $senior->id)
                ->count()
        )->toBe(1);
    });

    test('a note already on an edge survives a save that keeps it', function () {
        /*
         * The pivot carries how long the step usually takes, and
         * the form has no field for it. Resyncing the whole
         * relation would drop it on every unrelated save.
         */
        $role = pathRole('Application Developer');
        $senior = pathRole('Senior Developer', 2);

        $role->progressesTo()->attach($senior->id, [
            'notes' => 'Typically after three years.',
        ]);

        $this->actingAs($this->admin)
            ->put(
                route('admin.biicf.job-roles.update', $role),
                rolePayload(['progresses_to' => [$senior->id]])
            );

        expect(
            DB::table('biicf_career_path_edges')
                ->where('from_job_role_id', $role->id)
                ->where('to_job_role_id', $senior->id)
                ->value('notes')
        )->toBe('Typically after three years.');
    });
});

describe('the forms', function () {
    test('the edit form offers the other roles and ticks the current ones', function () {
        $role = pathRole('Application Developer');
        $senior = pathRole('Senior Developer', 2);

        $role->progressesTo()->attach($senior->id);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.job-roles.edit', $role))
            ->assertOk()
            ->assertShows('name="progresses_to[]"')
            ->assertShows('name="progresses_from[]"')
            ->assertShows(
                'value="'.$senior->id.'" data-cp-box="progresses_to" checked'
            )
            /* Its own row is not offered, in either column. */
            ->assertHides('value="'.$role->id.'" data-cp-box');
    });

    test('the create form offers the picker before any role exists', function () {
        /*
         * With nothing to connect to it says so rather than
         * showing two empty boxes.
         */
        $this->actingAs($this->admin)
            ->get(route('admin.biicf.job-roles.create'))
            ->assertOk()
            ->assertShows('There is no other job role to connect this one to yet.')
            ->assertHides('name="progresses_to[]"');

        pathRole('Senior Developer', 2);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.job-roles.create'))
            ->assertOk()
            ->assertShows('name="progresses_to[]"');
    });

    test('the role page shows the progression without opening the form', function () {
        $role = pathRole('Application Developer');
        $senior = pathRole('Senior Developer', 2);

        $role->progressesTo()->attach($senior->id);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.job-roles.show', $role))
            ->assertOk()
            ->assertShows('Leads To')
            ->assertShows('Senior Developer')
            /* And says which way round an empty one is. */
            ->assertShows('Nothing recorded');
    });
});
