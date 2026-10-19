<?php

use App\Models\BiicfEdition;
use App\Models\BiicfEditionLocation;
use App\Models\BiicfJobRole;
use App\Models\User;
use App\Services\Biicf\EditionLocationWriter;
use Database\Seeders\BiicfJobRoleSeeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Checking the framework against the book.
 *
 * Not against the extraction. The review screen stages the
 * fields where the book and the framework disagree, which is the
 * wrong shape for checking: once an edition has been applied
 * there are no disagreements left, so there was nothing to look
 * at. Checking is what you do before you know whether anything
 * is wrong, so every value the book states has its place kept —
 * agreed or not.
 */

function bookedEdition(): BiicfEdition
{
    return BiicfEdition::create([
        'name' => 'Second Edition',
        'slug' => 'second-edition-check',
        'edition_number' => 9,
        'source_file_path' => 'biicf-editions/second.pdf',
        'source_original_name' => 'biicf-second-edition.pdf',
    ]);
}

/**
 * The reader's map, in the shape it hands over.
 *
 * @return array<string, array<string, array<string, mixed>>>
 */
function locatedAt(string $title): array
{
    return [
        $title => [
            'description' => [
                'page' => 55,
                'spans' => [['x' => 39.0, 'y' => 238.5, 'text' => 'Oversee and manage the operation']],
            ],
            'trainings' => [
                'page' => 57,
                'spans' => [['x' => 430.9, 'y' => 210.5, 'text' => 'Datacom - HCIA-Datacom']],
            ],
        ],
    ];
}

beforeEach(function () {
    Storage::fake('local');

    $this->seed(BiicfJobRoleSeeder::class);

    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->edition = bookedEdition();
    $this->role = BiicfJobRole::where('title', 'Infrastructure Manager')->sole();
});

describe('keeping where the book says it', function () {
    test('every value the book states is located, agreed or not', function () {
        $written = (new EditionLocationWriter)->write($this->edition, locatedAt($this->role->title));

        expect($written)->toBe(2)
            ->and(BiicfEditionLocation::where('edition_id', $this->edition->id)->count())->toBe(2);
    });

    test('reading the book again replaces what it recorded before', function () {
        /*
         * A reader corrected since the last reading should
         * correct every place it kept. Merged instead, a field
         * the new reading does not reach would keep a page
         * number from the version before.
         */
        $writer = new EditionLocationWriter;

        $writer->write($this->edition, [
            $this->role->title => [
                'description' => ['page' => 1, 'spans' => [['x' => 1.0, 'y' => 1.0, 'text' => 'wrong']]],
                'tiers' => ['page' => 2, 'spans' => [['x' => 2.0, 'y' => 2.0, 'text' => 'gone next time']]],
            ],
        ]);

        $writer->write($this->edition, locatedAt($this->role->title));

        $kept = BiicfEditionLocation::where('edition_id', $this->edition->id)
            ->pluck('page', 'field');

        expect($kept)->toHaveCount(2)
            ->and($kept['description'])->toBe(55)
            ->and($kept)->not->toHaveKey('tiers');
    });

    test('a place with no page is not a place', function () {
        $written = (new EditionLocationWriter)->write($this->edition, [
            $this->role->title => [
                'description' => ['spans' => [['x' => 1.0, 'y' => 1.0, 'text' => 'no page']]],
                'trainings' => ['page' => 57],
            ],
        ]);

        expect($written)->toBe(0);
    });
});

describe('the checking screen', function () {
    test('it puts the value next to the page it is printed on', function () {
        (new EditionLocationWriter)->write($this->edition, locatedAt($this->role->title));

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.editions.book', $this->edition))
            ->assertOk()
            ->assertSee('Check against the book')
            ->assertSee('Job description')
            ->assertSee('Recommended courses')
            ->assertSee('p.55')
            ->assertSee('p.57')
            /* The book draws into the column, not over the screen. */
            ->assertSee('data-into="#bookPane"', false)
            ->assertSee('id="bookPane"', false);
    });

    test('the framework own wording is shown, not the book reading of it', function () {
        /*
         * The whole point. An administrator checking a value
         * wants what students are being shown, against the page
         * — not what a reader made of the page.
         */
        (new EditionLocationWriter)->write($this->edition, locatedAt($this->role->title));

        $held = $this->role->descriptionPoints->first()?->text;

        expect($held)->not->toBeNull();

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.editions.book', $this->edition))
            ->assertOk()
            /* Escaped as Blade escapes it, so an apostrophe cannot fail this. */
            ->assertSee(e(Str::limit($held, 40, '')), false);
    });

    test('a role the framework has lost is still listed, and says so', function () {
        (new EditionLocationWriter)->write($this->edition, locatedAt('A Role Nobody Holds'));

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.editions.book', $this->edition))
            ->assertOk()
            ->assertSee('A Role Nobody Holds')
            ->assertSee('The framework no longer holds a role by this name');
    });

    test('an edition read before the places were kept says what to do', function () {
        /*
         * Nothing needs uploading again — the file is on record
         * and the reader knows these. Saying "nothing here" and
         * leaving it there would send somebody looking for a
         * book they already have.
         */
        $this->actingAs($this->admin)
            ->get(route('admin.biicf.editions.book', $this->edition))
            ->assertOk()
            ->assertSee('Nothing to check against yet')
            ->assertSee('Read the book again');
    });

    test('an edition with no book behind it has nothing to check against', function () {
        $edition = BiicfEdition::create([
            'name' => 'Pasted Edition',
            'slug' => 'pasted-edition-check',
            'edition_number' => 10,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.biicf.editions.book', $edition))
            ->assertNotFound();
    });

    test('a student cannot open it', function () {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get(route('admin.biicf.editions.book', $this->edition))
            ->assertForbidden();
    });
});
