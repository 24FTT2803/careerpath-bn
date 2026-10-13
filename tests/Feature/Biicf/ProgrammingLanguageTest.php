<?php

use App\Models\BiicfJobRole;
use App\Models\BiicfTraining;
use App\Models\User;
use App\Services\Biicf\JobRoleContentUpdater;
use App\Services\Biicf\ProgrammingLanguageWriter;
use Database\Seeders\BiicfJobRoleSeeder;
use Illuminate\Support\Facades\DB;

/*
 * The languages a role is expected to know.
 *
 * The book gives them a section of their own, between the
 * recommended courses and the competency tables. They were
 * stored as courses, which is wrong twice over: nothing offers
 * or certifies a language, and the flattening cut the list in
 * half where the page wrapped.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
});

test('the role the book names them for has them, and not as courses', function () {
    $role = BiicfJobRole::where('title', 'Applications Developer')->sole();

    expect($role->programmingLanguages->pluck('name')->all())
        ->toBe([
            'JavaScript', 'Python', 'HTML', 'CSS', 'Java', 'SQL', 'NoSQL',
            'C#', 'C++', 'Rust', 'Perl', 'Go', 'PHP', 'R', 'Ruby', 'Swift',
        ])
        ->and($role->trainings->pluck('name')->filter(
            fn (string $name) => str_contains($name, 'Programming Languages')
                || str_contains($name, 'Ruby, Swift')
        )->all())->toBe([]);
});

test('no course anywhere in the framework is a list of languages', function () {
    expect(BiicfTraining::where('name', 'like', 'Programming Languages%')
        ->orWhere('name', 'like', '%Ruby, Swift)')
        ->count())->toBe(0);
});

test('a role the book says nothing about has none', function () {
    expect(BiicfJobRole::where('title', 'Data Entry')->sole()
        ->programmingLanguages)->toHaveCount(0);
});

test('writing the list again replaces it rather than adding to it', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    $writer = new ProgrammingLanguageWriter;

    expect($writer->write($role, ['Python', 'SQL']))->toBe(2)
        ->and($writer->write($role, ['Python', 'SQL']))->toBe(2)
        ->and($role->programmingLanguages()->count())->toBe(2);
});

test('the same language typed twice is stored once', function () {
    /*
     * The table will not hold one twice for a role, and a list
     * typed by hand is where the repeat comes from — so it is
     * dropped rather than allowed to fail the save.
     */
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    expect((new ProgrammingLanguageWriter)->write($role, ['Go', 'Go', ' Go ', '']))->toBe(1)
        ->and($role->programmingLanguages->pluck('name')->all())->toBe(['Go']);
});

test('the admin form keeps the order they were typed in', function () {
    $role = BiicfJobRole::where('title', 'Data Entry')->sole();

    (new JobRoleContentUpdater)->replaceProgrammingLanguages(
        $role,
        "Rust\n\nPython\n  Go  \n"
    );

    expect($role->programmingLanguages->pluck('name')->all())
        ->toBe(['Rust', 'Python', 'Go']);
});

test('an administrator can edit a role without clearing its languages', function () {
    /*
     * The field is written only when the form sends it. A page
     * that does not carry it — or a request from somewhere else
     * entirely — must not be read as an empty list.
     */
    $role = BiicfJobRole::where('title', 'Applications Developer')->sole();

    $was = $role->programmingLanguages->pluck('name')->all();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.biicf.job-roles.update', $role), [
            'sub_sector_id' => $role->sub_sector_id,
            'title' => $role->title,
            'job_description' => $role->descriptionPoints->pluck('text')->implode("\n"),
        ])
        ->assertRedirect();

    expect($role->refresh()->programmingLanguages->pluck('name')->all())->toBe($was);
});

test('deleting a role takes its languages with it', function () {
    $role = BiicfJobRole::where('title', 'Applications Developer')->sole();

    $id = $role->id;
    $role->delete();

    expect(DB::table('biicf_job_programming_languages')->where('job_role_id', $id)->count())
        ->toBe(0);
});
