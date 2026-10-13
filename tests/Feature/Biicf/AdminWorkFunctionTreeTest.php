<?php

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobRole;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * Editing a role's duties as a tree.
 *
 * They were a textarea, one duty per line, prefilled with the
 * generic ones alone. So a duty nested under another, or
 * belonging to one of the role's job families, was invisible to
 * the form — which is why deleting a duplicate there left its
 * copies standing, and why the data and the form said different
 * things about the same role.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

function treeRole(string $title = 'Data Entry'): BiicfJobRole
{
    return BiicfJobRole::where('title', $title)->sole();
}

/**
 * Save a tree against a role, the way the form does.
 *
 * @param  array<int, array<string, mixed>>  $tree
 */
function saveTree(User $admin, BiicfJobRole $role, array $tree)
{
    return test()->actingAs($admin)
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
            'work_functions' => json_encode($tree),
        ]);
}

describe('what the form is given', function () {
    test('the page carries every duty, nested, not just the generic ones', function () {
        $role = treeRole();

        $parent = BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'job_family_id' => $role->jobFamilies->first()?->id,
            'text' => 'A duty of one family',
            'sort_order' => 90,
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'parent_id' => $parent->id,
            'text' => 'A sub-duty under it',
            'sort_order' => 1,
        ]);

        $tree = $this->actingAs($this->admin)
            ->get(route('admin.biicf.job-roles.edit', $role))
            ->assertOk()
            ->viewData('workFunctionTree');

        $family = collect($tree)->firstWhere('text', 'A duty of one family');

        expect($family)->not->toBeNull()
            ->and($family['kind'])->toBe('specific')
            ->and($family['children'][0]['text'])->toBe('A sub-duty under it');
    });

    test('the tree holds only the tops of the lists, with children under them', function () {
        $tree = $this->actingAs($this->admin)
            ->get(route('admin.biicf.job-roles.edit', treeRole()))
            ->assertOk()
            ->viewData('workFunctionTree');

        expect(collect($tree)->pluck('text'))
            ->toHaveCount(treeRole()->criticalWorkFunctions()->whereNull('parent_id')->count());
    });
});

describe('saving it', function () {
    test('the nesting is kept', function () {
        $role = treeRole();

        saveTree($this->admin, $role, [[
            'text' => 'Run the help desk',
            'kind' => 'generic',
            'children' => [
                ['text' => 'Log every request', 'children' => [
                    ['text' => 'Record the diagnosis', 'children' => []],
                ]],
            ],
        ]])->assertRedirect();

        $top = BiicfCriticalWorkFunction::where('job_role_id', $role->id)
            ->whereNull('parent_id')
            ->sole();

        $middle = $top->children->sole();

        expect($top->text)->toBe('Run the help desk')
            ->and($middle->text)->toBe('Log every request')
            ->and($middle->children->sole()->text)->toBe('Record the diagnosis');
    });

    test('a duplicate removed in the form is gone everywhere', function () {
        /*
         * The complaint this editor exists for: the textarea could
         * not see a nested copy, so deleting the line it could see
         * left the others in the database.
         */
        $role = treeRole();

        $parent = BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'text' => 'Said twice',
            'sort_order' => 90,
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'parent_id' => $parent->id,
            'text' => 'Said twice',
            'sort_order' => 1,
        ]);

        expect(BiicfCriticalWorkFunction::where('job_role_id', $role->id)
            ->where('text', 'Said twice')->count())->toBe(2);

        saveTree($this->admin, $role, [['text' => 'Said once', 'children' => []]])->assertRedirect();

        expect(BiicfCriticalWorkFunction::where('job_role_id', $role->id)
            ->where('text', 'Said twice')->count())->toBe(0)
            ->and(BiicfCriticalWorkFunction::where('job_role_id', $role->id)->count())->toBe(1);
    });

    test('a sub-duty belongs to the family its parent belongs to', function () {
        $role = treeRole('IT Technician');
        $family = $role->jobFamilies->first();

        expect($family)->not->toBeNull();

        saveTree($this->admin, $role, [[
            'text' => 'Applications configuration',
            'kind' => 'specific',
            'job_family_id' => (string) $family->id,
            'children' => [
                ['text' => 'Follow the acceptance test procedure', 'children' => []],
            ],
        ]])->assertRedirect();

        $child = BiicfCriticalWorkFunction::where('job_role_id', $role->id)
            ->whereNotNull('parent_id')
            ->sole();

        expect($child->kind)->toBe('specific')
            ->and($child->job_family_id)->toBe($family->id);
    });

    test('a duty that is not specific keeps no family', function () {
        /*
         * Unticking "specific to a job family" while a family was
         * chosen would otherwise file the duty under a heading it
         * does not belong to.
         */
        $role = treeRole('IT Technician');

        saveTree($this->admin, $role, [[
            'text' => 'Something the role does however it is practised',
            'kind' => 'generic',
            'job_family_id' => (string) $role->jobFamilies->first()->id,
            'children' => [],
        ]])->assertRedirect();

        $written = BiicfCriticalWorkFunction::where('job_role_id', $role->id)->sole();

        expect($written->kind)->toBe('generic')
            ->and($written->job_family_id)->toBeNull();
    });

    test('an empty row is dropped, and takes its children with it', function () {
        /*
         * A row the form left open is not a duty. Keeping its
         * children would promote a sub-duty into a duty of the
         * role, which says something else entirely.
         */
        $role = treeRole();

        saveTree($this->admin, $role, [
            ['text' => 'A real duty', 'children' => []],
            ['text' => '   ', 'children' => [['text' => 'Orphan', 'children' => []]]],
        ])->assertRedirect();

        expect(BiicfCriticalWorkFunction::where('job_role_id', $role->id)->pluck('text')->all())
            ->toBe(['A real duty']);
    });

    test('a fourth level is not written', function () {
        $role = treeRole();

        saveTree($this->admin, $role, [[
            'text' => 'One',
            'children' => [['text' => 'Two', 'children' => [
                ['text' => 'Three', 'children' => [['text' => 'Four', 'children' => []]]],
            ]]],
        ]])->assertRedirect();

        expect(BiicfCriticalWorkFunction::where('job_role_id', $role->id)->pluck('text')->all())
            ->toBe(['One', 'Two', 'Three']);
    });

    test('the old text column is kept in step with the rows', function () {
        $role = treeRole();

        saveTree($this->admin, $role, [
            ['text' => 'First duty', 'children' => [['text' => 'Nested', 'children' => []]]],
            ['text' => 'Second duty', 'children' => []],
        ])->assertRedirect();

        expect($role->refresh()->critical_work_function)
            ->toBe("First duty\nSecond duty");
    });

    test('a save carrying neither the tree nor the textarea leaves the duties alone', function () {
        $role = treeRole();

        $was = $role->criticalWorkFunctions()->count();

        expect($was)->toBeGreaterThan(0);

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.job-roles.update', $role), [
                'sub_sector_id' => $role->sub_sector_id,
                'title' => $role->title,
            ])
            ->assertRedirect();

        expect($role->criticalWorkFunctions()->count())->toBe($was);
    });

    test('a tree that is not JSON is refused rather than clearing the role', function () {
        $role = treeRole();

        $was = $role->criticalWorkFunctions()->count();

        $this->actingAs($this->admin)
            ->put(route('admin.biicf.job-roles.update', $role), [
                'sub_sector_id' => $role->sub_sector_id,
                'title' => $role->title,
                'work_functions' => 'not json at all',
            ])
            ->assertSessionHasErrors('work_functions');

        expect($role->criticalWorkFunctions()->count())->toBe($was);
    });
});
