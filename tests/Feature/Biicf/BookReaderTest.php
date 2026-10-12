<?php

use App\Services\Biicf\Pdf\BookReader;
use App\Services\Biicf\Pdf\PageLayout;

/*
 * Reading the reference book.
 *
 * The fixture is three real pages of the second edition — one
 * role, Infrastructure Manager — recorded as what a PDF reader
 * actually hands over: a string and the point it starts at.
 * Deliberately no colour, no font size and no ruled lines, since
 * a position-only reader has none of those, and an earlier
 * reading of this book leaned on all three.
 *
 * So these tests cover the part that is hard and does not need a
 * PDF: which column a line belongs to, whether it opens a bullet
 * or continues one, and which level a figure in a table counts
 * against.
 */

function bookPages(): array
{
    $rows = json_decode(
        (string) file_get_contents(base_path('tests/Fixtures/biicf/role-pages.json')),
        true
    );

    ksort($rows, SORT_NUMERIC);

    return array_map(
        fn (array $runs, string $number) => PageLayout::from($runs, (int) $number),
        array_values($rows),
        array_keys($rows)
    );
}

function readBook(): array
{
    return (new BookReader)->read(bookPages());
}

describe('finding the roles', function () {
    test('a role is read from the pages it is printed on', function () {
        $book = readBook();

        expect($book['job_roles'])->toHaveCount(1)
            ->and($book['problems'])->toBe([]);

        $role = $book['job_roles'][0];

        expect($role['title'])->toBe('Infrastructure Manager')
            ->and($role['sub_sector'])->toBe('Telecommunications and Network')
            ->and($role['tiers'])->toBe(['Specialist']);
    });

    test('a value running to more lines than its label stays with it', function () {
        /*
         * The alternate titles are set over two lines and centred
         * against their label, so they begin above it. Splitting
         * the difference between one label and the next puts the
         * overflow into the sub-sector.
         */
        expect(readBook()['job_roles'][0]['alternate_titles'])->toBe([
            'Information Technology Infrastructure Manager',
            'Information Systems Manager',
            'Field Manager',
        ]);
    });
});

describe('bullets', function () {
    test('a bullet keeps the lines that wrap under it', function () {
        $description = readBook()['job_roles'][0]['description'];

        expect($description[1])->toBe(
            'Assist engineers and technical specialists in carrying out routine '
            .'wired or wireless network management tasks in line with corporate '
            .'objectives and industry best practices.'
        );
    });

    test('every column of the page is read, and none of them interleave', function () {
        /*
         * The book sets three columns to a page and runs its text
         * through them. Read straight down the page instead of
         * column by column, the columns interleave and a sentence
         * comes out holding words from two of them — which is how
         * one role's duties were once filed under another's.
         */
        $description = readBook()['job_roles'][0]['description'];

        expect($description[0])->toBe(
            'Oversee and manage the operation and upkeep of the telecom infrastructure.'
        );

        expect($description)->toContain(
            'Implement and maintain physical and cybersecurity measures to safeguard '
            .'servers and sensitive data, including access control, monitoring and '
            .'compliance with security policies.'
        );

        expect($description)->toContain(
            'Resolve more intricate and unusual network problems.'
        );
    });

    test('the running foot and the contents button are not text', function () {
        $role = readBook()['job_roles'][0];

        $everything = implode(' ', [
            implode(' ', $role['description']),
            implode(' ', $role['trainings']),
        ]);

        expect($everything)
            ->not->toContain('Brunei ICT Industry Competency Framework')
            ->not->toContain('Go to Table of Contents');
    });
});

describe('critical work functions', function () {
    test('duties are kept under the group they are printed beneath', function () {
        $groups = readBook()['job_roles'][0]['critical_work_functions'];

        expect(collect($groups)->pluck('name')->all())
            ->toContain('Infrastructure Planning and Architecture')
            ->toContain('Resource Management');

        $planning = collect($groups)->firstWhere('name', 'Infrastructure Planning and Architecture');

        expect($planning['duties'][0])->toBe(
            'Create an infrastructure strategy for the organisation that '
            .'complies with current laws and requirements.'
        );
    });

    test('a group heading set over two lines is one heading', function () {
        $names = collect(readBook()['job_roles'][0]['critical_work_functions'])->pluck('name');

        expect($names)->toContain('Infrastructure Governance and Compliance Management')
            ->and($names)->not->toContain('Management');
    });
});

describe('the competency tables', function () {
    test('a competency is read against the level its figure sits under', function () {
        $competencies = collect(readBook()['job_roles'][0]['competencies']);

        $one = $competencies->firstWhere('name', 'Business Risk Management');

        expect($one)->not->toBeNull()
            ->and($one['type'])->toBe('technical')
            ->and($one['levels'])->toBe(['Specialist' => 5]);
    });

    test('a soft skill is counted on its own scale', function () {
        /*
         * Advanced is the third of four rungs, not the third of
         * five. Counting both scales on one ladder makes a soft
         * skill look like a gap it is not.
         */
        $resilience = collect(readBook()['job_roles'][0]['competencies'])
            ->firstWhere('name', 'Resilience');

        expect($resilience['type'])->toBe('soft_skill')
            ->and($resilience['levels'])->toBe(['Specialist' => 3]);
    });

    test('the tables do not leak into the training courses', function () {
        /*
         * A role's soft skills are set as a table whose figures
         * sit at the far right of the page, past the column
         * holding the courses. Followed as text rather than read
         * as a table, the proficiencies come out as courses.
         */
        $trainings = readBook()['job_roles'][0]['trainings'];

        foreach ($trainings as $course) {
            expect($course)->not->toContain('Advanced')
                ->and(mb_strlen($course))->toBeLessThanOrEqual(255);
        }
    });
});

describe('entry requirements', function () {
    test('each way in is read as its own alternative', function () {
        $ways = readBook()['job_roles'][0]['entry_requirements']['Specialist'];

        expect($ways)->toHaveCount(2)
            ->and($ways[0]['raw_text'])->toStartWith('Brunei Darussalam Qualification Framework (BDQF) Level 6')
            ->and($ways[1]['raw_text'])->toStartWith('Brunei Darussalam Qualification Framework (BDQF) Level 5');
    });
});

describe('refusing what it cannot read', function () {
    test('a file with no role pages in it is refused', function () {
        expect(fn () => (new BookReader)->read([
            PageLayout::from([
                ['t' => 'A page of something else entirely.', 'x' => 50, 'y' => 50],
            ], 0),
        ]))->toThrow(RuntimeException::class);
    });
});
