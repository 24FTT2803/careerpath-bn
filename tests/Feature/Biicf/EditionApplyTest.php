<?php

use App\Models\BiicfEdition;
use App\Models\BiicfEditionChange;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\User;
use App\Services\Biicf\EditionApplier;
use App\Services\Biicf\EditionComparer;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * Applying an edition once its differences have been decided.
 *
 * The decisions are the administrator's; this only carries them
 * out. What matters is that it carries out exactly what was
 * decided, that a kept value is left alone, and that nothing is
 * applied while anything is still undecided.
 */

function applyEdition(): BiicfEdition
{
    return BiicfEdition::create([
        'name' => 'Third Edition',
        'slug' => 'third-edition',
        'edition_number' => 3,
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 */
function stagedChange(BiicfEdition $edition, array $overrides = []): BiicfEditionChange
{
    return $edition->changes()->create(array_merge([
        'subject_type' => 'job_role',
        'subject_key' => 'Data Entry',
        'subject_label' => 'Data Entry',
        'field' => 'description',
        'kind' => BiicfEditionChange::KIND_CHANGED,
        'current_value' => ['What it says now.'],
        'proposed_value' => ['What the new book says.'],
        'decision' => BiicfEditionChange::DECISION_ACCEPT,
        'decided_at' => now(),
    ], $overrides));
}

/**
 * The framework read back in the shape an extracted edition
 * arrives in, so a test can change one thing and stage it.
 *
 * @return array<string, mixed>
 */
function frameworkAsEdition(): array
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

    return ['job_roles' => $roles, 'competencies' => []];
}

beforeEach(function () {
    $this->seed(BiicfJobRoleSeeder::class);
    $this->edition = applyEdition();
});

describe('before anything is applied', function () {
    test('an edition with undecided changes is refused', function () {
        stagedChange($this->edition, [
            'decision' => BiicfEditionChange::DECISION_PENDING,
            'decided_at' => null,
        ]);

        expect(fn () => (new EditionApplier)->apply($this->edition))
            ->toThrow(RuntimeException::class, 'nobody has decided');
    });

    test('an edition cannot be applied twice', function () {
        stagedChange($this->edition);
        (new EditionApplier)->apply($this->edition);

        expect(fn () => (new EditionApplier)->apply($this->edition->fresh()))
            ->toThrow(RuntimeException::class, 'already been applied');
    });
});

describe('carrying out a decision', function () {
    test('an accepted change is written', function () {
        stagedChange($this->edition);

        (new EditionApplier)->apply($this->edition);

        expect(
            BiicfJobRole::where('title', 'Data Entry')
                ->sole()
                ->descriptionPoints
                ->pluck('text')
                ->all()
        )->toBe(['What the new book says.']);
    });

    test('an edited change writes what the administrator wrote', function () {
        stagedChange($this->edition, [
            'decision' => BiicfEditionChange::DECISION_EDIT,
            'edited_value' => ['What the administrator wrote.'],
        ]);

        (new EditionApplier)->apply($this->edition);

        expect(
            BiicfJobRole::where('title', 'Data Entry')
                ->sole()
                ->descriptionPoints
                ->pluck('text')
                ->all()
        )->toBe(['What the administrator wrote.']);
    });

    test('a kept change leaves the framework alone', function () {
        $before = BiicfJobRole::where('title', 'Data Entry')
            ->sole()
            ->descriptionPoints
            ->pluck('text')
            ->all();

        $change = stagedChange($this->edition, [
            'decision' => BiicfEditionChange::DECISION_KEEP,
        ]);

        (new EditionApplier)->apply($this->edition);

        expect(
            BiicfJobRole::where('title', 'Data Entry')->sole()->descriptionPoints->pluck('text')->all()
        )->toBe($before)
            /*
             * Marked applied all the same: the decision was
             * made, and the framework already agrees with it.
             */
            ->and($change->fresh()->applied_at)->not->toBeNull();
    });

    test('a proficiency is written against its own tier', function () {
        $role = BiicfJobRole::where('title', 'IT Technician')->sole();

        stagedChange($this->edition, [
            'subject_key' => 'IT Technician',
            'subject_label' => 'IT Technician',
            'field' => 'competencies.Application Development.Level 2',
            'proposed_value' => 5,
        ]);

        (new EditionApplier)->apply($this->edition);

        $levels = $role->fresh()->tiers->map(
            fn ($tier) => BiicfProficiencyLevel::find(
                $tier->competencies->firstWhere('name', 'Application Development')
                    ->pivot
                    ->proficiency_level_id
            )->level_number
        );

        expect($levels->all())->toBe([1, 5, 2]);
    });

    test('a competency the edition drops is detached from the tier', function () {
        $role = BiicfJobRole::where('title', 'Data Entry')->sole();
        $name = $role->entryTier->competencies->first()->name;

        stagedChange($this->edition, [
            'field' => "competencies.{$name}.Entrant",
            'kind' => BiicfEditionChange::KIND_REMOVED,
            'proposed_value' => null,
        ]);

        (new EditionApplier)->apply($this->edition);

        expect(
            $role->fresh()->entryTier->competencies->contains('name', $name)
        )->toBeFalse();
    });

    test('a role the edition drops is retired, not deleted', function () {
        /*
         * A student's saved recommendation points at it, and the
         * framework retiring a role does not unmake the advice
         * someone was given while it was current.
         */
        stagedChange($this->edition, [
            'field' => '*',
            'kind' => BiicfEditionChange::KIND_REMOVED,
            'current_value' => ['title' => 'Data Entry'],
            'proposed_value' => null,
        ]);

        (new EditionApplier)->apply($this->edition);

        $role = BiicfJobRole::where('title', 'Data Entry')->sole();

        expect($role->exists)->toBeTrue()
            ->and($role->retired_in_edition_id)->toBe($this->edition->id)
            ->and($role->isRetired())->toBeTrue();
    });

    test('a sub-sector spelled differently is the same sub-sector', function () {
        /*
         * The book prints "Telecommunications and Network" on
         * four pages and "Telecommunications and Networks" on
         * two. Matched exactly, the plural made a seventh
         * sub-sector and moved two roles into it, splitting a
         * part of the framework in half — and a sub-sector takes
         * its roles with it when deleted, so tidying that up by
         * hand is one click from losing them.
         */
        $before = BiicfSubSector::count();

        $role = BiicfJobRole::where('title', 'Data Entry')->sole();

        stagedChange($this->edition, [
            'subject_key' => 'Data Entry',
            'subject_label' => 'Data Entry',
            'field' => 'sub_sector',
            'kind' => BiicfEditionChange::KIND_CHANGED,
            'current_value' => $role->subSector->name,
            'proposed_value' => $role->subSector->name.'s',
        ]);

        (new EditionApplier)->apply($this->edition);

        expect(BiicfSubSector::count())->toBe($before)
            ->and($role->fresh()->sub_sector_id)->toBe($role->sub_sector_id);
    });

    test('a sub-sector the framework really lacks is added at the end', function () {
        /*
         * Not at nought, which is where the first one already
         * sits.
         */
        stagedChange($this->edition, [
            'subject_key' => 'Data Entry',
            'subject_label' => 'Data Entry',
            'field' => 'sub_sector',
            'kind' => BiicfEditionChange::KIND_CHANGED,
            'current_value' => 'Data and Artificial Intelligence',
            'proposed_value' => 'Quantum Computing',
        ]);

        $highest = (int) BiicfSubSector::max('sort_order');

        (new EditionApplier)->apply($this->edition);

        $added = BiicfSubSector::where('name', 'Quantum Computing')->sole();

        expect($added->sort_order)->toBe($highest + 1);
    });

    test('a role the edition adds is created whole', function () {
        stagedChange($this->edition, [
            'subject_key' => 'Quantum Systems Engineer',
            'subject_label' => 'Quantum Systems Engineer',
            'field' => '*',
            'kind' => BiicfEditionChange::KIND_ADDED,
            'current_value' => null,
            'proposed_value' => [
                'title' => 'Quantum Systems Engineer',
                'sub_sector' => 'IT Services',
                'alternate_titles' => ['Qubit Wrangler'],
                'description' => ['Builds what is both built and not.'],
                'trainings' => ['Quantum Foundations'],
                'tiers' => ['Specialist'],
                'entry_requirements' => [
                    'Specialist' => [['raw_text' => 'BDQF Level 6 in Physics']],
                ],
                'competencies' => [],
            ],
        ]);

        (new EditionApplier)->apply($this->edition);

        $role = BiicfJobRole::where('title', 'Quantum Systems Engineer')->sole();

        expect($role->descriptionPoints)->toHaveCount(1)
            ->and($role->tiers->pluck('label')->all())->toBe(['Specialist'])
            ->and($role->trainings->pluck('name')->all())->toBe(['Quantum Foundations'])
            ->and($role->entryRequirement->raw_text)->toBe('BDQF Level 6 in Physics')
            ->and($role->alternative_titles)->toBe(['Qubit Wrangler']);
    });
});

describe('the edition itself', function () {
    test('applying makes it the current one', function () {
        $previous = BiicfEdition::create([
            'name' => 'Second Edition',
            'slug' => 'second-edition-live',
            'edition_number' => 2,
            'is_current' => true,
        ]);

        stagedChange($this->edition);

        (new EditionApplier)->apply($this->edition, User::factory()->create(['role' => 'admin']));

        expect($this->edition->fresh()->is_current)->toBeTrue()
            ->and($this->edition->fresh()->applied_at)->not->toBeNull()
            ->and($previous->fresh()->is_current)->toBeFalse();
    });

    test('the outgoing edition keeps what it described', function () {
        /*
         * So a report generated against it can still say what it
         * was generated against, after the live tables move on.
         */
        $previous = BiicfEdition::create([
            'name' => 'Second Edition',
            'slug' => 'second-edition-live',
            'edition_number' => 2,
            'is_current' => true,
        ]);

        $before = BiicfJobRole::where('title', 'Data Entry')
            ->sole()
            ->descriptionPoints
            ->pluck('text')
            ->all();

        stagedChange($this->edition);

        (new EditionApplier)->apply($this->edition);

        $entry = collect($previous->fresh()->snapshot_data['job_roles'])
            ->firstWhere('title', 'Data Entry');

        expect($entry['description'])->toBe($before)
            ->and($entry['tiers'])->toBe(['Entrant'])
            ->and($entry['description'])->not->toBe(['What the new book says.']);
    });

    test('what was applied is stamped with the edition it came from', function () {
        stagedChange($this->edition);

        (new EditionApplier)->apply($this->edition);

        $role = BiicfJobRole::where('title', 'Data Entry')->sole();

        expect($role->source_edition_id)->toBe($this->edition->id)
            ->and($role->isImported())->toBeTrue();
    });
});

describe('end to end', function () {
    test('a staged difference survives being compared, decided and applied', function () {
        $payload = frameworkAsEdition();
        $index = collect($payload['job_roles'])->search(
            fn ($role) => $role['title'] === 'Data Entry'
        );
        $payload['job_roles'][$index]['description'] = ['A description from the third edition.'];

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()->sole();

        $change->update([
            'decision' => BiicfEditionChange::DECISION_ACCEPT,
            'decided_at' => now(),
        ]);

        $written = (new EditionApplier)->apply($this->edition->fresh());

        expect($written)->toBe(1)
            ->and(
                BiicfJobRole::where('title', 'Data Entry')
                    ->sole()
                    ->descriptionPoints
                    ->pluck('text')
                    ->all()
            )->toBe(['A description from the third edition.']);
    });
});
