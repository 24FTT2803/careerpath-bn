<?php

use App\Models\BiicfEdition;
use App\Models\BiicfEditionChange;
use App\Models\BiicfJobRole;
use App\Services\Biicf\EditionComparer;
use Database\Seeders\BiicfJobRoleSeeder;

/*
 * What an uploaded edition proposes to change.
 *
 * The framework updates by publishing a new book, and most of a
 * new edition is the old one. What an administrator needs is
 * what differs — and nothing reaching students until they have
 * said so, one difference at a time.
 */

function stagedEdition(): BiicfEdition
{
    return BiicfEdition::create([
        'name' => 'Third Edition',
        'slug' => 'third-edition',
        'edition_number' => 3,
    ]);
}

/**
 * The current framework, read back in the shape an extractor
 * produces, so a test can change one thing and compare.
 *
 * @return array<string, mixed>
 */
function currentAsPayload(): array
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
    $this->edition = stagedEdition();
});

describe('an edition that matches what is loaded', function () {
    test('it proposes nothing', function () {
        /*
         * The strongest check available: the comparer reading
         * the framework against itself should find no
         * differences at all. Anything it does find is a field
         * it reads differently than it writes.
         */
        (new EditionComparer)->compare($this->edition, currentAsPayload());

        $changes = $this->edition->changes()
            ->get()
            ->map(fn ($change) => $change->subject_label.' · '.$change->field)
            ->all();

        expect($changes)->toBe([]);
    });

    test('unless everything is asked for', function () {
        /*
         * Which is how an edition is loaded onto an empty or
         * badly drifted database: every field proposed, agreeing
         * or not.
         */
        $count = (new EditionComparer)->compare(
            $this->edition,
            currentAsPayload(),
            EditionComparer::STRATEGY_EVERYTHING
        );

        expect($count)->toBeGreaterThan(100);
    });
});

describe('what it notices', function () {
    test('a rewritten description', function () {
        $payload = currentAsPayload();
        $payload['job_roles'][0]['description'] = ['Something else entirely.'];

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()->sole();

        expect($change->field)->toBe('description')
            ->and($change->kind)->toBe(BiicfEditionChange::KIND_CHANGED)
            ->and($change->proposed_value)->toBe(['Something else entirely.'])
            ->and($change->current_value)->not->toBe(['Something else entirely.']);
    });

    test('a proficiency raised by one rung', function () {
        /*
         * Per cell, not per table: one competency moving should
         * read as that one competency moving.
         */
        $payload = currentAsPayload();
        $role = collect($payload['job_roles'])->search(
            fn ($role) => $role['title'] === 'IT Technician'
        );

        $payload['job_roles'][$role]['competencies'][0]['levels']['Level 1'] = 5;

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()->sole();

        expect($change->subject_label)->toBe('IT Technician')
            ->and($change->field)->toStartWith('competencies.')
            ->and($change->field)->toEndWith('.Level 1')
            ->and($change->proposed_value)->toBe(5);
    });

    test('a role the framework did not have', function () {
        $payload = currentAsPayload();
        $payload['job_roles'][] = [
            'title' => 'Quantum Systems Engineer',
            'sub_sector' => 'IT Services',
            'alternate_titles' => [],
            'description' => ['Builds things that are both built and not.'],
            'trainings' => [],
            'tiers' => ['Specialist'],
            'entry_requirements' => [],
            'competencies' => [],
        ];

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()->sole();

        expect($change->kind)->toBe(BiicfEditionChange::KIND_ADDED)
            ->and($change->field)->toBe('*')
            ->and($change->subject_label)->toBe('Quantum Systems Engineer');
    });

    test('a role the new edition drops', function () {
        /*
         * Proposed as a removal rather than acted on: the
         * framework retires a role, and a student's saved
         * recommendation still points at it.
         */
        $payload = currentAsPayload();
        $payload['job_roles'] = collect($payload['job_roles'])
            ->reject(fn ($role) => $role['title'] === 'Data Entry')
            ->values()
            ->all();

        (new EditionComparer)->compare($this->edition, $payload);

        $change = $this->edition->changes()
            ->where('kind', BiicfEditionChange::KIND_REMOVED)
            ->sole();

        expect($change->subject_label)->toBe('Data Entry');

        expect(BiicfJobRole::where('title', 'Data Entry')->exists())->toBeTrue();
    });

    test('a competency a role no longer requires', function () {
        $payload = currentAsPayload();
        $index = collect($payload['job_roles'])->search(
            fn ($role) => $role['title'] === 'Data Entry'
        );

        $dropped = $payload['job_roles'][$index]['competencies'][0]['name'];
        array_shift($payload['job_roles'][$index]['competencies']);

        (new EditionComparer)->compare($this->edition, $payload);

        $changes = $this->edition->changes()
            ->where('kind', BiicfEditionChange::KIND_REMOVED)
            ->get();

        expect($changes)->not->toBeEmpty()
            ->and($changes->first()->field)->toContain($dropped);
    });
});

describe('the strategies', function () {
    test('filling gaps leaves what is already recorded alone', function () {
        /*
         * Which is what makes it safe to run over a framework an
         * administrator has been correcting by hand.
         */
        $payload = currentAsPayload();
        $payload['job_roles'][0]['description'] = ['Something else entirely.'];

        $count = (new EditionComparer)->compare(
            $this->edition,
            $payload,
            EditionComparer::STRATEGY_GAPS
        );

        expect($count)->toBe(0);
    });

    test('filling gaps still proposes what is missing', function () {
        $role = BiicfJobRole::where('title', 'Data Entry')->sole();
        $role->descriptionPoints()->delete();

        $count = (new EditionComparer)->compare(
            $this->edition,
            currentAsPayload(),
            EditionComparer::STRATEGY_GAPS
        );

        expect($count)->toBe(1)
            ->and($this->edition->changes()->sole()->field)->toBe('description');
    });

    test('filling gaps never proposes a removal', function () {
        $payload = currentAsPayload();
        $payload['job_roles'] = collect($payload['job_roles'])
            ->reject(fn ($role) => $role['title'] === 'Data Entry')
            ->values()
            ->all();

        $count = (new EditionComparer)->compare(
            $this->edition,
            $payload,
            EditionComparer::STRATEGY_GAPS
        );

        expect($count)->toBe(0);
    });
});

describe('re-running an extraction', function () {
    test('it does not stack duplicate proposals', function () {
        $payload = currentAsPayload();
        $payload['job_roles'][0]['description'] = ['Something else entirely.'];

        $comparer = new EditionComparer;
        $comparer->compare($this->edition, $payload);
        $comparer->compare($this->edition, $payload);

        expect($this->edition->changes()->count())->toBe(1);
    });

    test('it keeps decisions already taken', function () {
        /*
         * An administrator part way through a review should not
         * lose their work because the extraction was run again.
         */
        $payload = currentAsPayload();
        $payload['job_roles'][0]['description'] = ['Something else entirely.'];

        $comparer = new EditionComparer;
        $comparer->compare($this->edition, $payload);

        $this->edition->changes()->sole()->update([
            'decision' => BiicfEditionChange::DECISION_KEEP,
            'decided_at' => now(),
        ]);

        $comparer->compare($this->edition, $payload);

        expect($this->edition->changes()->sole()->decision)
            ->toBe(BiicfEditionChange::DECISION_KEEP);
    });
});

describe('what a decision would write', function () {
    test('accepting writes what the book says', function () {
        $change = new BiicfEditionChange([
            'decision' => BiicfEditionChange::DECISION_ACCEPT,
            'proposed_value' => ['The book.'],
            'edited_value' => ['The administrator.'],
        ]);

        expect($change->outcome())->toBe(['The book.'])
            ->and($change->writes())->toBeTrue();
    });

    test('editing writes what the administrator wrote', function () {
        $change = new BiicfEditionChange([
            'decision' => BiicfEditionChange::DECISION_EDIT,
            'proposed_value' => ['The book.'],
            'edited_value' => ['The administrator.'],
        ]);

        expect($change->outcome())->toBe(['The administrator.']);
    });

    test('keeping writes nothing', function () {
        /*
         * A recorded decision all the same: it is how an
         * administrator says the book is wrong about something
         * they have already corrected.
         */
        $change = new BiicfEditionChange([
            'decision' => BiicfEditionChange::DECISION_KEEP,
            'proposed_value' => ['The book.'],
        ]);

        expect($change->writes())->toBeFalse()
            ->and($change->isDecided())->toBeTrue();
    });
});
