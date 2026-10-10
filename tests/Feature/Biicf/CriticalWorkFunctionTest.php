<?php

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobDescriptionPoint;
use App\Models\BiicfJobFamily;
use App\Models\BiicfJobRole;
use App\Models\BiicfSubSector;
use Database\Seeders\BiicfExplorerSeeder;
use Illuminate\Support\Str;

/*
 * A role's duties as structure rather than text.
 *
 * The book divides critical work functions into generic ones
 * and family-specific ones, and nests a second level of bullets
 * under some families. A newline-separated column could record
 * a flat list and nothing else, so what matters here is that
 * the distinctions survive at all.
 */

function workFunctionRole(string $title = 'IT Technician'): BiicfJobRole
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

function workFamily(string $name): BiicfJobFamily
{
    return BiicfJobFamily::firstOrCreate(
        ['slug' => Str::slug($name)],
        ['name' => $name]
    );
}

describe('generic and specific functions', function () {
    test('they are kept apart', function () {
        $role = workFunctionRole();
        $family = workFamily('Desktop and Application Support');

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'text' => 'Install, maintain and upgrade desktop hardware.',
            'sort_order' => 1,
        ]);

        BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'job_family_id' => $family->id,
            'text' => 'Install and test new ICT equipment.',
            'sort_order' => 1,
        ]);

        expect($role->genericWorkFunctions)->toHaveCount(1)
            ->and($role->specificWorkFunctions)->toHaveCount(1)
            ->and($role->genericWorkFunctions->first()->isGeneric())
            ->toBeTrue()
            ->and($role->specificWorkFunctions->first()->jobFamily->name)
            ->toBe('Desktop and Application Support');
    });

    test('a generic function belongs to no family', function () {
        /*
         * Generic functions apply however the role is
         * practised, which is the distinction being recorded.
         */
        $function = BiicfCriticalWorkFunction::create([
            'job_role_id' => workFunctionRole()->id,
            'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
            'text' => 'Record keeping of incidents and service requests.',
        ]);

        expect($function->job_family_id)->toBeNull();
    });

    test('specific functions group under their families', function () {
        /*
         * IT Technician's specific functions are printed under
         * three family headings, which is the layout this
         * grouping reproduces.
         */
        $role = workFunctionRole();

        foreach ([
            'Desktop and Application Support' => 'Perform basic PC hardware repairs.',
            'Server and Network Support' => 'Install and maintain standard network cabling.',
        ] as $familyName => $text) {
            BiicfCriticalWorkFunction::create([
                'job_role_id' => $role->id,
                'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
                'job_family_id' => workFamily($familyName)->id,
                'text' => $text,
            ]);
        }

        $byFamily = $role->specificWorkFunctions
            ->load('jobFamily')
            ->groupBy(fn ($function) => $function->jobFamily->name);

        expect($byFamily)->toHaveCount(2)
            ->and($byFamily['Server and Network Support']->first()->text)
            ->toBe('Install and maintain standard network cabling.');
    });
});

describe('nested functions', function () {
    test('a function can carry sub-functions', function () {
        /*
         * The book's deepest level: a heading duty with its own
         * bullets underneath.
         */
        $role = workFunctionRole();

        $heading = BiicfCriticalWorkFunction::create([
            'job_role_id' => $role->id,
            'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
            'job_family_id' => workFamily('Applications Configuration')->id,
            'text' => 'Configuration and Installation',
            'sort_order' => 1,
        ]);

        foreach ([
            'Follow an acceptance test procedure on new ICT equipment.',
            'Update records of installed hardware and software.',
        ] as $index => $text) {
            BiicfCriticalWorkFunction::create([
                'job_role_id' => $role->id,
                'kind' => BiicfCriticalWorkFunction::KIND_SPECIFIC,
                'job_family_id' => $heading->job_family_id,
                'parent_id' => $heading->id,
                'text' => $text,
                'sort_order' => $index + 1,
            ]);
        }

        expect($heading->children)->toHaveCount(2)
            ->and($heading->children->first()->parent->text)
            ->toBe('Configuration and Installation');
    });

    test('a listing returns headings rather than every row', function () {
        /*
         * Without this, a sub-bullet would appear twice: once
         * under its heading and once alongside it.
         */
        $role = workFunctionRole();

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

        expect($role->criticalWorkFunctions)->toHaveCount(2)
            ->and($role->genericWorkFunctions)->toHaveCount(1);
    });

    test('removing a heading removes what sat under it', function () {
        $role = workFunctionRole();

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

        $heading->delete();

        expect(BiicfCriticalWorkFunction::count())->toBe(0);
    });
});

describe('job description points', function () {
    test('a description is a list in order', function () {
        $role = workFunctionRole();

        foreach ([
            'Provide support functions for technology equipment.',
            'Maintain functioning information technology equipment.',
        ] as $index => $text) {
            BiicfJobDescriptionPoint::create([
                'job_role_id' => $role->id,
                'text' => $text,
                'sort_order' => $index + 1,
            ]);
        }

        expect($role->descriptionPoints->pluck('text')->all())->toBe([
            'Provide support functions for technology equipment.',
            'Maintain functioning information technology equipment.',
        ]);
    });
});

describe('the backfill from text', function () {
    test('seeded roles carry their duties as rows', function () {
        /*
         * The migration splits what is already stored, and the
         * seeder writes the text columns, so a seeded database
         * should hold both. They are not attributed to a family
         * or nested, because the text recorded neither.
         */
        $this->seed(BiicfExplorerSeeder::class);

        $role = BiicfJobRole::where('slug', 'it-technician')->sole();

        expect($role->criticalWorkFunctions->count())->toBeGreaterThan(1)
            ->and($role->genericWorkFunctions->count())
            ->toBe($role->criticalWorkFunctions->count())
            ->and($role->specificWorkFunctions)->toHaveCount(0);
    });

    test('the original text is left untouched', function () {
        /*
         * Nothing has moved off the old columns yet, so the
         * pages still reading them keep working.
         */
        $this->seed(BiicfExplorerSeeder::class);

        $role = BiicfJobRole::where('slug', 'it-technician')->sole();

        expect($role->critical_work_function)->not->toBeEmpty()
            ->and($role->job_description)->not->toBeEmpty();
    });
});
