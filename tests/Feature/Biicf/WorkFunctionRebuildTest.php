<?php

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobRole;
use App\Services\Biicf\WorkFunctionWriter;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * Rebuilding a role's duties.
 *
 * One database held two and a half thousand duty rows where the
 * data describes eleven hundred — the same groups two and three
 * times, each copy a reading from a different day. Writing
 * without clearing did it.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
});

test('writing a role\'s duties replaces them rather than adding to them', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    $functions = [
        ['text' => 'A duty group', 'children' => [
            ['text' => 'A duty.', 'children' => []],
            ['text' => 'Another duty.', 'children' => []],
        ]],
    ];

    $writer = new WorkFunctionWriter;

    expect($writer->write($role, $functions))->toBe(3);

    /* Again, with the same thing to say. */
    expect($writer->write($role, $functions))->toBe(3)
        ->and($role->criticalWorkFunctions()->count())->toBe(3);
});

test('the nesting survives the rebuild', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    (new WorkFunctionWriter)->write($role, [
        ['text' => 'Group', 'children' => [
            ['text' => 'Duty', 'children' => []],
        ]],
    ]);

    $group = BiicfCriticalWorkFunction::where('job_role_id', $role->id)
        ->whereNull('parent_id')
        ->sole();

    expect($group->text)->toBe('Group')
        ->and(BiicfCriticalWorkFunction::where('parent_id', $group->id)->sole()->text)
        ->toBe('Duty');
});

test('the command rebuilds every role to what the data file describes', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    $was = $role->criticalWorkFunctions()->count();

    /* Duplicate them, the way they came to be duplicated. */
    (new WorkFunctionWriter)->write($role, [['text' => 'Spurious', 'children' => []]]);
    BiicfCriticalWorkFunction::create([
        'job_role_id' => $role->id,
        'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
        'text' => 'Spurious',
        'sort_order' => 2,
    ]);

    expect($role->criticalWorkFunctions()->count())->toBe(2);

    $this->artisan('biicf:rebuild-work-functions')->assertSuccessful();

    expect($role->criticalWorkFunctions()->count())->toBe($was)
        ->and(BiicfCriticalWorkFunction::where('text', 'Spurious')->exists())->toBeFalse();
});

test('pretending writes nothing', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    (new WorkFunctionWriter)->write($role, [['text' => 'Spurious', 'children' => []]]);

    $this->artisan('biicf:rebuild-work-functions', ['--pretend' => true])->assertSuccessful();

    expect(BiicfCriticalWorkFunction::where('text', 'Spurious')->exists())->toBeTrue();
});

test('a rewrite leaves nothing from the reading before it, however deep', function () {
    /*
     * One DELETE for the whole role is what this used to do, and
     * it does not work on MySQL: parent_id is a foreign key onto
     * the same table with ON DELETE CASCADE, and InnoDB does not
     * apply that cascade reliably when parent and child are both
     * within reach of one multi-row statement. Three readings of
     * the book survived three rewrites that way — every row at
     * sort_order 1 went with its sub-duties, and the rest stayed.
     *
     * The writer clears the leaves first now, so no delete ever
     * cascades. SQLite does the right thing either way, which is
     * why this stayed green while the real database drifted; what
     * this pins is that the clearing happens at all.
     */
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    $deep = [[
        'text' => 'A group',
        'children' => [[
            'text' => 'A duty',
            'children' => [
                ['text' => 'A sub-duty', 'children' => []],
                ['text' => 'Another sub-duty', 'children' => []],
            ],
        ]],
    ]];

    $writer = new WorkFunctionWriter;

    expect($writer->write($role, $deep))->toBe(4);

    expect($writer->write($role, [['text' => 'Something else', 'children' => []]]))->toBe(1)
        ->and($role->criticalWorkFunctions()->count())->toBe(1)
        ->and(BiicfCriticalWorkFunction::where('job_role_id', $role->id)->pluck('text')->all())
        ->toBe(['Something else']);
});

test('forgetting a role\'s duties clears every level of them', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    (new WorkFunctionWriter)->write($role, [[
        'text' => 'A group',
        'children' => [['text' => 'A duty', 'children' => [
            ['text' => 'A sub-duty', 'children' => []],
        ]]],
    ]]);

    expect((new WorkFunctionWriter)->forget($role))->toBe(3)
        ->and(BiicfCriticalWorkFunction::where('job_role_id', $role->id)->count())->toBe(0);
});
