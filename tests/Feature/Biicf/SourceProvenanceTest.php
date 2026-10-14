<?php

use App\Models\BiicfEdition;
use App\Models\BiicfJobRole;
use App\Services\Biicf\EditionComparer;
use App\Services\Biicf\Pdf\BookReader;
use App\Services\Biicf\Pdf\PageLayout;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * Pointing back at the book.
 *
 * An administrator deciding a difference is being asked which of
 * two values is right. The reader knew where it read the book's
 * one — it reads the book by position and nothing else — and
 * used to throw that away on the way out, leaving three hundred
 * pages to search by hand.
 */

/**
 * @return array<int, PageLayout>
 */
function provenancePages(): array
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

/**
 * @return array<string, array<string, mixed>>
 */
function placesInTheBook(): array
{
    $book = (new BookReader)->read(provenancePages());

    return $book['source_locations']['Infrastructure Manager'] ?? [];
}

describe('what the reader remembers', function () {
    test('every value it read carries the page it was printed on', function () {
        $at = placesInTheBook();

        /*
         * The fixture is three real pages of the second edition,
         * and the role's parts are printed across all three: the
         * label block and duties on the first, the work
         * functions on the second, the tables on the third.
         */
        expect($at['sub_sector']['page'])->toBe(55)
            ->and($at['description']['page'])->toBe(55)
            ->and($at['critical_work_functions']['page'])->toBe(56)
            ->and($at['entry_requirements']['page'])->toBe(57)
            ->and($at['competencies.Cloud Computing']['page'])->toBe(57);
    });

    test('a position is where the words are, not where a flag was left', function () {
        /*
         * A line the reader decided was a heading within its
         * column has its indent overwritten with -1 to mark it.
         * Read as a position that puts the box off the left edge
         * of the page.
         */
        $at = placesInTheBook();

        $spans = collect($at)->flatMap(fn (array $place) => $place['spans']);

        expect($spans)->not->toBeEmpty()
            ->and($spans->filter(fn (array $span) => $span['x'] < 0))->toBeEmpty();
    });

    test('a span carries the words themselves, so a viewer can find them', function () {
        /*
         * Two readers of one PDF need not agree about where the
         * origin of a page is — one counts up from the foot, the
         * other down from the head — but they do agree about
         * what it says. The text is what makes the match
         * possible when the arithmetic does not.
         */
        $at = placesInTheBook();

        expect($at['sub_sector']['spans'][0]['text'])->toBe('Sub-Sector')
            ->and($at['competencies.Cloud Computing']['spans'][0]['text'])
            ->toBe('Cloud Computing');
    });

    test('a long section keeps enough to point at and no more', function () {
        /*
         * A role's work functions run to sixty lines. Every one
         * of them, against every field of every role, is a
         * column of JSON larger than the framework it describes.
         */
        $at = placesInTheBook();

        expect(count($at['critical_work_functions']['spans']))
            ->toBeGreaterThan(1)
            ->toBeLessThanOrEqual(12);
    });

    test('a role the book has never printed has no place in it', function () {
        $book = (new BookReader)->read(provenancePages());

        expect($book['source_locations'])->toHaveCount(1)
            ->and($book['source_locations'])->toHaveKey('Infrastructure Manager');
    });
});

describe('what a staged difference carries', function () {
    beforeEach(function () {
        $this->seed(BiicfJobRoleSeeder::class);

        $this->edition = BiicfEdition::create([
            'name' => 'Third Edition',
            'slug' => 'third-edition-provenance',
            'edition_number' => 3,
        ]);
    });

    test('a difference says where in the book the proposal came from', function () {
        $role = BiicfJobRole::where('title', 'Infrastructure Manager')->sole();

        $payload = [
            'job_roles' => [[
                'title' => $role->title,
                'sub_sector' => 'Something Else Entirely',
            ]],
            'competencies' => [],
            'source_locations' => [
                $role->title => [
                    'sub_sector' => [
                        'page' => 55,
                        'spans' => [['x' => 311.3, 'y' => 77.5, 'text' => 'Sub-Sector']],
                    ],
                ],
            ],
        ];

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()->where('field', 'sub_sector')->sole();

        expect($change->source_location['page'])->toBe(55)
            ->and($change->source_location['spans'][0]['text'])->toBe('Sub-Sector');
    });

    test('a field more precise than the book falls back to what the book has', function () {
        /*
         * A proficiency is staged as
         * "competencies.Cloud Computing.Specialist" and the book
         * prints it as a row: one place, every tier's cell in
         * it.
         */
        $role = BiicfJobRole::where('title', 'Infrastructure Manager')->sole();

        $payload = [
            'job_roles' => [[
                'title' => $role->title,
                'competencies' => [[
                    'name' => 'Cloud Computing',
                    'type' => 'technical',
                    'levels' => ['Specialist' => 1],
                ]],
            ]],
            'competencies' => [],
            'source_locations' => [
                $role->title => [
                    'competencies.Cloud Computing' => [
                        'page' => 57,
                        'spans' => [['x' => 45.4, 'y' => 158.8, 'text' => 'Cloud Computing']],
                    ],
                ],
            ],
        ];

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()
            ->where('field', 'competencies.Cloud Computing.Specialist')
            ->sole();

        expect($change->source_location['page'])->toBe(57);
    });

    test('a role the edition dropped does not inherit the last role read', function () {
        /*
         * The subject compared before it was somewhere in the
         * book; this one is in no edition at all, and a page
         * number on it would send an administrator to a page
         * about something else.
         */
        $payload = [
            'job_roles' => [[
                'title' => 'Infrastructure Manager',
                'sub_sector' => 'Something Else Entirely',
            ]],
            'competencies' => [],
            'source_locations' => [
                'Infrastructure Manager' => [
                    'sub_sector' => [
                        'page' => 55,
                        'spans' => [['x' => 311.3, 'y' => 77.5, 'text' => 'Sub-Sector']],
                    ],
                ],
            ],
        ];

        (new EditionComparer)->compare($this->edition, $payload);

        $dropped = $this->edition->changes()
            ->where('kind', 'removed')
            ->where('field', '*')
            ->first();

        expect($dropped)->not->toBeNull()
            ->and($dropped->source_location)->toBeNull();
    });

    test('an edition with no positions in it stages differences all the same', function () {
        /*
         * A JSON edition has no pages. The review screen then
         * has nothing to offer, which is not the same as the
         * upload failing.
         */
        $role = BiicfJobRole::where('title', 'Infrastructure Manager')->sole();

        (new EditionComparer)->compare($this->edition, [
            'job_roles' => [[
                'title' => $role->title,
                'sub_sector' => 'Something Else Entirely',
            ]],
            'competencies' => [],
        ]);

        $change = $this->edition->changes()->where('field', 'sub_sector')->sole();

        expect($change->source_location)->toBeNull();
    });
});
