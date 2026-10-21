<?php

use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobRole;
use App\Services\Biicf\EntryRequirementWriter;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * Rebuilding the ways in to a role.
 *
 * The book prints some roles' entry requirements once and others
 * under a heading for each level. An earlier reading gave every
 * level all of them, so a database loaded from it tells IT
 * Technician's Level 1 it wants what Level 3 wants. The reader
 * was corrected; this is how the correction reaches data already
 * written.
 */

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
});

function bookNaming(array $byTier): string
{
    $path = sys_get_temp_dir().'/edition-'.uniqid().'.json';

    file_put_contents($path, json_encode([
        'job_roles' => [[
            'title' => 'IT Technician',
            'sub_sector' => 'Infrastructure',
            'tiers' => array_keys($byTier),
            'entry_requirements' => $byTier,
        ]],
    ]));

    return $path;
}

function waysInTo(string $title, string $tier): array
{
    $tier = BiicfJobRole::where('title', $title)->sole()
        ->tiers()->where('label', $tier)->sole();

    return $tier->entryRequirements()
        ->orderBy('alternative_number')
        ->pluck('raw_text')
        ->all();
}

describe('writing them', function () {
    test('each level keeps only the ways in it was given', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $written = (new EntryRequirementWriter)->write($role, [
            'Level 1' => [['raw_text' => 'BDQF Level 2 in IT']],
            'Level 3' => [
                ['raw_text' => 'BDQF Level 4 in IT'],
                ['raw_text' => 'BDQF Level 3 in IT with a certification'],
            ],
        ]);

        expect($written)->toBe(3)
            ->and(waysInTo('IT Technician', 'Level 1'))->toBe(['BDQF Level 2 in IT'])
            ->and(waysInTo('IT Technician', 'Level 3'))->toBe([
                'BDQF Level 4 in IT',
                'BDQF Level 3 in IT with a certification',
            ]);
    });

    test('a level the book says nothing about is left alone', function () {
        /*
         * A reading that skipped a level used to be indistinguishable
         * from one that cleared it, and clearing is the more
         * destructive reading of silence.
         */
        $was = waysInTo('IT Technician', 'Level 2');

        (new EntryRequirementWriter)->write(
            BiicfJobRole::where('title', 'IT Technician')->sole(),
            ['Level 1' => [['raw_text' => 'BDQF Level 2 in IT']]]
        );

        expect(waysInTo('IT Technician', 'Level 2'))->toBe($was)
            ->and($was)->not->toBe([]);
    });

    test('a way in added by hand outlives the book', function () {
        /*
         * An administrator may list a route the framework does not.
         * Emptying the level before writing would delete it every
         * time the book was read again.
         */
        $role = BiicfJobRole::where('title', 'Data Entry')->sole();
        $tier = $role->tiers()->where('label', 'Entrant')->sole();

        BiicfEntryRequirement::create([
            'job_role_tier_id' => $tier->id,
            'alternative_number' => $tier->entryRequirements()->count() + 1,
            'raw_text' => 'A route the polytechnic accepts and the book does not',
        ]);

        (new EntryRequirementWriter)->write($role, [
            'Entrant' => [['raw_text' => 'BDQF Level 3 in IT']],
        ]);

        expect(waysInTo('Data Entry', 'Entrant'))->toBe([
            'BDQF Level 3 in IT',
            'A route the polytechnic accepts and the book does not',
        ]);
    });

    test('a requirement naming a level the role does not have is refused', function () {
        expect(fn () => (new EntryRequirementWriter)->write(
            BiicfJobRole::where('title', 'Data Entry')->sole(),
            ['Expert/Management' => [['raw_text' => 'Something']]]
        ))->toThrow(RuntimeException::class, 'Expert/Management');
    });
});

describe('the rebuild', function () {
    test('every level ends up with what the book gives it', function () {
        $book = bookNaming([
            'Level 1' => [['raw_text' => 'BDQF Level 2 in IT']],
            'Level 2' => [['raw_text' => 'BDQF Level 3 in IT']],
            'Level 3' => [
                ['raw_text' => 'BDQF Level 4 in IT'],
                ['raw_text' => 'BDQF Level 3 in IT with two years'],
            ],
        ]);

        $this->artisan('biicf:rebuild-entry-requirements', ['--book' => $book])
            ->assertSuccessful();

        expect(waysInTo('IT Technician', 'Level 1'))->toBe(['BDQF Level 2 in IT'])
            ->and(waysInTo('IT Technician', 'Level 2'))->toBe(['BDQF Level 3 in IT'])
            ->and(waysInTo('IT Technician', 'Level 3'))->toHaveCount(2);

        unlink($book);
    });

    test('nothing else about the role is touched', function () {
        /*
         * The point of a rebuild rather than another apply: every
         * decision taken while reviewing the edition stands, and
         * only the requirements are read again.
         */
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        $was = [
            $role->criticalWorkFunctions()->count(),
            $role->trainings()->count(),
            $role->descriptionPoints()->count(),
        ];

        $book = bookNaming(['Level 1' => [['raw_text' => 'BDQF Level 2 in IT']]]);

        $this->artisan('biicf:rebuild-entry-requirements', ['--book' => $book])
            ->assertSuccessful();

        $role->refresh();

        expect([
            $role->criticalWorkFunctions()->count(),
            $role->trainings()->count(),
            $role->descriptionPoints()->count(),
        ])->toBe($was);

        unlink($book);
    });

    test('pretending writes nothing', function () {
        $was = waysInTo('IT Technician', 'Level 1');

        $book = bookNaming(['Level 1' => [['raw_text' => 'Something else entirely']]]);

        $this->artisan('biicf:rebuild-entry-requirements', [
            '--book' => $book,
            '--pretend' => true,
        ])->assertSuccessful();

        expect(waysInTo('IT Technician', 'Level 1'))->toBe($was);

        unlink($book);
    });

    test('a book at a path with nothing on it is refused', function () {
        $this->artisan('biicf:rebuild-entry-requirements', [
            '--book' => '/no/such/book.pdf',
        ])->assertFailed();
    });

    test('a role in the book and not in the framework is reported, not created', function () {
        $path = sys_get_temp_dir().'/edition-'.uniqid().'.json';

        file_put_contents($path, json_encode([
            'job_roles' => [[
                'title' => 'Quantum Plumber',
                'sub_sector' => 'Infrastructure',
                'tiers' => ['Entrant'],
                'entry_requirements' => ['Entrant' => [['raw_text' => 'A degree']]],
            ]],
        ]));

        $before = BiicfJobRole::count();

        $this->artisan('biicf:rebuild-entry-requirements', ['--book' => $path])
            ->expectsOutputToContain('not in this framework')
            ->assertSuccessful();

        expect(BiicfJobRole::count())->toBe($before);

        unlink($path);
    });
});
