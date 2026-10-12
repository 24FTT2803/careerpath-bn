<?php

use App\Models\BiicfEdition;
use App\Models\BiicfEditionChange;
use App\Models\BiicfJobRole;
use App\Models\User;
use Database\Seeders\BiicfJobRoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
 * The screens an administrator loads an edition through.
 *
 * Upload, read, review, apply — kept apart on purpose. The
 * framework is what every recommendation rests on, so nothing an
 * upload says reaches a student until someone has been through
 * the differences.
 */

function editionAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function uploadedEdition(array $overrides = []): UploadedFile
{
    $roles = collect(json_decode(
        (string) file_get_contents(database_path('data/biicf-job-roles.json')),
        true
    ))->map(fn (array $role) => [
        'title' => $role['title'],
        'sub_sector' => $role['sub_sector'],
        'alternate_titles' => $role['alternate_titles'],
        'description' => $role['description'],
        'trainings' => $role['trainings'],
        'tiers' => $role['tiers'],
        'entry_requirements' => $role['entry_requirements'],
        'competencies' => $role['competencies'],
    ])->all();

    foreach ($overrides as $title => $changes) {
        $at = collect($roles)->search(fn ($role) => $role['title'] === $title);
        $roles[$at] = array_merge($roles[$at], $changes);

        /*
         * A null drops the field entirely, which is different
         * from setting it empty: the book prints some roles with
         * no recommended courses at all, and a reader that could
         * not make sense of a field leaves it out rather than
         * proposing a guess.
         */
        foreach ($changes as $field => $value) {
            if ($value === null) {
                unset($roles[$at][$field]);
            }
        }
    }

    return UploadedFile::fake()->createWithContent(
        'third-edition.json',
        json_encode(['job_roles' => $roles, 'competencies' => []])
    );
}

/**
 * @param  array<string, mixed>  $overrides
 */
function postEdition(array $overrides = [], string $strategy = 'changed')
{
    return test()->actingAs(editionAdmin())
        ->post(route('admin.biicf.editions.store'), [
            'name' => 'Third Edition',
            'edition_number' => 3,
            'strategy' => $strategy,
            'source' => uploadedEdition($overrides),
        ]);
}

beforeEach(function () {
    Storage::fake('local');
    $this->seed(BiicfJobRoleSeeder::class);
});

describe('uploading', function () {
    test('an edition is staged with its differences', function () {
        postEdition(['Data Entry' => ['description' => ['A third edition description.']]])
            ->assertRedirect();

        $edition = BiicfEdition::sole();

        expect($edition->name)->toBe('Third Edition')
            ->and($edition->applied_at)->toBeNull()
            ->and($edition->is_current)->toBeFalse()
            ->and($edition->changes()->count())->toBe(1);
    });

    test('nothing is written to the framework by uploading', function () {
        $before = BiicfJobRole::where('title', 'Data Entry')
            ->sole()
            ->descriptionPoints
            ->pluck('text')
            ->all();

        postEdition(['Data Entry' => ['description' => ['A third edition description.']]]);

        expect(
            BiicfJobRole::where('title', 'Data Entry')->sole()->descriptionPoints->pluck('text')->all()
        )->toBe($before);
    });

    test('a field the edition does not state is left alone', function () {
        /*
         * Not the same as a field stating nothing. The book
         * prints IT Technician with no recommended courses, and
         * a reader that cannot make sense of a field leaves it
         * out. Read as an empty list instead, the edition
         * proposes deleting every course the framework holds —
         * and an administrator working down a long list could
         * approve that without ever seeing what it was.
         */
        $before = BiicfJobRole::where('title', 'Data Entry')
            ->sole()
            ->trainings
            ->pluck('name')
            ->all();

        postEdition(['Data Entry' => ['trainings' => null]])->assertRedirect();

        expect(BiicfEdition::sole()->changes()->where('field', 'trainings')->count())->toBe(0)
            ->and($before)->not->toBeEmpty();
    });

    test('a role the book prints twice is compared once and reported', function () {
        /*
         * The book prints Chief Technology Officer under two
         * sub-sectors with different duties under each. The
         * framework holds one role per title, so the second has
         * nowhere to go — and compared as well it collides with
         * the first on every field it touches.
         */
        $roles = collect(json_decode(
            (string) file_get_contents(database_path('data/biicf-job-roles.json')),
            true
        ))->map(fn (array $role) => [
            'title' => $role['title'],
            'sub_sector' => $role['sub_sector'],
            'description' => $role['description'],
            'tiers' => $role['tiers'],
        ])->all();

        /*
         * The first printing says something new, so there is a
         * difference to find; the second says something else
         * again. Only the first should be compared.
         */
        $at = collect($roles)->search(fn (array $role) => $role['title'] === 'Data Entry');
        $roles[$at]['description'] = ['What the first printing says.'];

        $twice = array_merge($roles, [[
            'title' => 'Data Entry',
            'sub_sector' => 'Digital Media',
            'description' => ['What the second printing says.'],
            'tiers' => ['Entrant'],
        ]]);

        $this->actingAs(editionAdmin())
            ->post(route('admin.biicf.editions.store'), [
                'name' => 'Third Edition',
                'edition_number' => 3,
                'strategy' => 'changed',
                'source' => UploadedFile::fake()->createWithContent(
                    'third-edition.json',
                    json_encode(['job_roles' => $twice, 'competencies' => []])
                ),
            ])
            ->assertRedirect()
            ->assertSessionHas('problems', fn (array $problems) => collect($problems)
                ->contains(fn (string $problem) => str_contains($problem, 'twice')));

        $description = BiicfEdition::sole()
            ->changes()
            ->where('subject_key', 'Data Entry')
            ->where('field', 'description')
            ->sole();

        expect($description->proposed_value)->toBe(['What the first printing says.']);
    });

    test('a competency spelled with a different capital is the same competency', function () {
        /*
         * Market Analyst's page prints "Decision-making" where
         * the framework holds "Decision-Making". Matched
         * exactly, that reads as one competency added and
         * another removed — and since the two differ only in
         * case, and the index identifying a change does not,
         * the pair collide and the whole edition fails.
         */
        $book = collect(json_decode(
            (string) file_get_contents(database_path('data/biicf-job-roles.json')),
            true
        ))->firstWhere('title', 'Data Entry');

        /*
         * Every competency as the book has it, except one: spelled
         * in lower case, and at a different proficiency, so there
         * is exactly one difference to find and it must be filed
         * against the framework's own spelling.
         */
        $competencies = $book['competencies'];
        $renamed = $competencies[0];
        $tier = array_key_first($renamed['levels']);

        $competencies[0]['name'] = mb_strtolower($renamed['name']);
        $competencies[0]['levels'][$tier] = $renamed['levels'][$tier] === 1 ? 2 : 1;

        postEdition(['Data Entry' => ['competencies' => $competencies]])->assertRedirect();

        $raised = BiicfEdition::sole()
            ->changes()
            ->where('subject_key', 'Data Entry')
            ->where('field', 'like', 'competencies.%')
            ->get();

        expect($raised)->toHaveCount(1)
            ->and($raised->first()->field)
            ->toBe("competencies.{$renamed['name']}.{$tier}")
            ->and($raised->first()->kind)
            ->toBe(BiicfEditionChange::KIND_CHANGED);
    });

    test('a file that is not an edition is refused', function () {
        /*
         * And the edition goes with it: a half-made edition in
         * the list, holding nothing, is worse than none.
         */
        $this->actingAs(editionAdmin())
            ->from(route('admin.biicf.editions.index'))
            ->post(route('admin.biicf.editions.store'), [
                'name' => 'Third Edition',
                'edition_number' => 3,
                'strategy' => 'changed',
                'source' => UploadedFile::fake()->createWithContent(
                    'notes.json',
                    json_encode(['something' => 'else'])
                ),
            ])
            ->assertSessionHasErrors('source');

        expect(BiicfEdition::count())->toBe(0);
    });

    test('an edition number cannot be reused', function () {
        postEdition();

        $this->actingAs(editionAdmin())
            ->from(route('admin.biicf.editions.index'))
            ->post(route('admin.biicf.editions.store'), [
                'name' => 'Third Edition Again',
                'edition_number' => 3,
                'strategy' => 'changed',
                'source' => uploadedEdition(),
            ])
            ->assertSessionHasErrors('edition_number');
    });

    test('the book an edition was read from can be opened', function () {
        /*
         * Every difference on the review screen is a claim about
         * what the book says. Whoever decides those claims should
         * be able to go and look.
         */
        postEdition();

        $this->actingAs(editionAdmin())
            ->get(route('admin.biicf.editions.source', BiicfEdition::sole()))
            ->assertSuccessful();
    });

    test('only an administrator can read the book', function () {
        postEdition();

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get(route('admin.biicf.editions.source', BiicfEdition::sole()))
            ->assertForbidden();
    });

    test('only an administrator can upload one', function () {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->post(route('admin.biicf.editions.store'), [
                'name' => 'Third Edition',
                'edition_number' => 3,
                'strategy' => 'changed',
                'source' => uploadedEdition(),
            ])
            ->assertForbidden();
    });
});

describe('reviewing', function () {
    beforeEach(function () {
        postEdition(['Data Entry' => ['description' => ['A third edition description.']]]);
        $this->edition = BiicfEdition::sole();
        $this->change = $this->edition->changes()->sole();
    });

    test('the review screen shows both values', function () {
        $this->actingAs(editionAdmin())
            ->get(route('admin.biicf.editions.show', $this->edition))
            ->assertSuccessful()
            ->assertSee('A third edition description.')
            ->assertSee('Data Entry');
    });

    test('a decision is recorded against the change', function () {
        $admin = editionAdmin();

        $this->actingAs($admin)
            ->patch(
                route('admin.biicf.editions.decide', [
                    'edition' => $this->edition,
                    'change' => $this->change,
                ]),
                ['decision' => 'keep']
            )
            ->assertRedirect();

        $change = $this->change->fresh();

        expect($change->decision)->toBe(BiicfEditionChange::DECISION_KEEP)
            ->and($change->decided_by)->toBe($admin->id)
            ->and($change->decided_at)->not->toBeNull();
    });

    test('an edited value is read as the lines it replaces', function () {
        /*
         * The field being replaced is a list, so what is typed
         * is read as one — a textarea cannot say whether it
         * holds one value or several.
         */
        $this->actingAs(editionAdmin())
            ->patch(
                route('admin.biicf.editions.decide', [
                    'edition' => $this->edition,
                    'change' => $this->change,
                ]),
                [
                    'decision' => 'edit',
                    'edited_value' => "First point.\n\nSecond point.",
                ]
            )
            ->assertRedirect();

        expect($this->change->fresh()->edited_value)
            ->toBe(['First point.', 'Second point.']);
    });

    test('editing without a value is refused', function () {
        $this->actingAs(editionAdmin())
            ->from(route('admin.biicf.editions.show', $this->edition))
            ->patch(
                route('admin.biicf.editions.decide', [
                    'edition' => $this->edition,
                    'change' => $this->change,
                ]),
                ['decision' => 'edit']
            )
            ->assertSessionHasErrors('edited_value');
    });

    test('a decision answers the page rather than redirecting it', function () {
        /*
         * A hundred and twenty differences is a lot of scrolling
         * to lose on every click, so the page records a decision
         * where it was made. What it needs back is the decision
         * and how many are left.
         */
        $this->actingAs(editionAdmin())
            ->patchJson(
                route('admin.biicf.editions.decide', [
                    'edition' => $this->edition,
                    'change' => $this->change,
                ]),
                ['decision' => 'keep', 'ajax' => '1']
            )
            ->assertSuccessful()
            ->assertJson([
                'decision' => BiicfEditionChange::DECISION_KEEP,
                'pending' => 0,
            ]);
    });

    test('an edited decision answers with what was written', function () {
        $this->actingAs(editionAdmin())
            ->patchJson(
                route('admin.biicf.editions.decide', [
                    'edition' => $this->edition,
                    'change' => $this->change,
                ]),
                [
                    'decision' => 'edit',
                    'edited_value' => "First point.\nSecond point.",
                    'ajax' => '1',
                ]
            )
            ->assertSuccessful()
            ->assertJson([
                'decision' => BiicfEditionChange::DECISION_EDIT,
                'edited' => ['First point.', 'Second point.'],
            ]);
    });

    test('everything left over can be decided at once', function () {
        $this->actingAs(editionAdmin())
            ->patch(
                route('admin.biicf.editions.decide-all', $this->edition),
                ['decision' => 'accept']
            )
            ->assertRedirect();

        expect($this->edition->changes()->pending()->count())->toBe(0);
    });

    test('a change cannot be decided through another edition', function () {
        $other = BiicfEdition::create([
            'name' => 'Fourth Edition',
            'slug' => 'fourth-edition',
            'edition_number' => 4,
        ]);

        $this->actingAs(editionAdmin())
            ->patch(
                route('admin.biicf.editions.decide', [
                    'edition' => $other,
                    'change' => $this->change,
                ]),
                ['decision' => 'accept']
            )
            ->assertNotFound();
    });
});

describe('discarding', function () {
    beforeEach(function () {
        postEdition(['Data Entry' => ['description' => ['A third edition description.']]]);
        $this->edition = BiicfEdition::sole();
    });

    test('an edition staged by mistake can be thrown away', function () {
        $this->actingAs(editionAdmin())
            ->delete(route('admin.biicf.editions.destroy', $this->edition))
            ->assertRedirect(route('admin.biicf.editions.index'));

        expect(BiicfEdition::count())->toBe(0)
            ->and(BiicfEditionChange::count())->toBe(0);
    });

    test('its number can be used again afterwards', function () {
        /*
         * The reason this exists: an edition left behind by a
         * failed read holds its number, and nothing else says so.
         */
        $this->actingAs(editionAdmin())
            ->delete(route('admin.biicf.editions.destroy', $this->edition));

        postEdition()->assertRedirect();

        expect(BiicfEdition::sole()->edition_number)->toBe(3);
    });

    test('an applied edition is kept, because it is the record', function () {
        $this->actingAs(editionAdmin())
            ->patch(route('admin.biicf.editions.decide-all', $this->edition), ['decision' => 'accept']);

        $this->actingAs(editionAdmin())
            ->post(route('admin.biicf.editions.apply', $this->edition));

        $this->actingAs(editionAdmin())
            ->from(route('admin.biicf.editions.index'))
            ->delete(route('admin.biicf.editions.destroy', $this->edition))
            ->assertSessionHasErrors('delete');

        expect(BiicfEdition::whereKey($this->edition->id)->exists())->toBeTrue();
    });

    test('only an administrator can discard one', function () {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->delete(route('admin.biicf.editions.destroy', $this->edition))
            ->assertForbidden();
    });
});

describe('applying', function () {
    beforeEach(function () {
        postEdition(['Data Entry' => ['description' => ['A third edition description.']]]);
        $this->edition = BiicfEdition::sole();
    });

    test('an edition with anything undecided is refused', function () {
        $this->actingAs(editionAdmin())
            ->from(route('admin.biicf.editions.show', $this->edition))
            ->post(route('admin.biicf.editions.apply', $this->edition))
            ->assertSessionHasErrors('apply');

        expect($this->edition->fresh()->applied_at)->toBeNull();
    });

    test('a decided edition reaches the framework', function () {
        $this->actingAs(editionAdmin())
            ->patch(
                route('admin.biicf.editions.decide-all', $this->edition),
                ['decision' => 'accept']
            );

        $this->actingAs(editionAdmin())
            ->post(route('admin.biicf.editions.apply', $this->edition))
            ->assertRedirect(route('admin.biicf.editions.index'));

        expect(
            BiicfJobRole::where('title', 'Data Entry')
                ->sole()
                ->descriptionPoints
                ->pluck('text')
                ->all()
        )->toBe(['A third edition description.'])
            ->and($this->edition->fresh()->is_current)->toBeTrue();
    });

    test('only an administrator can apply one', function () {
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->post(route('admin.biicf.editions.apply', $this->edition))
            ->assertForbidden();
    });
});
