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
