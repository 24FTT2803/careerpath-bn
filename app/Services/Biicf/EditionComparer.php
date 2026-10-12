<?php

namespace App\Services\Biicf;

use App\Models\BiicfCompetency;
use App\Models\BiicfEdition;
use App\Models\BiicfEditionChange;
use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;

/**
 * Work out what an uploaded edition would change.
 *
 * The comparison is per field rather than per record: an
 * administrator reviewing a new edition wants to see that one
 * role's entry requirement has been rewritten, not that the role
 * "changed". Each difference becomes a row they can accept, keep
 * or edit, and nothing is written to the live tables here.
 *
 * Three strategies, as the framework's own updates vary in how
 * much they touch:
 *
 *  - everything: propose the book's value for every field, even
 *    where it already agrees, for an edition being loaded onto
 *    an empty or badly drifted database.
 *  - changed: propose only where the book and the database
 *    disagree. The normal case.
 *  - gaps: propose only where the database holds nothing, so an
 *    edition can fill in what was never recorded without
 *    touching anything an administrator has written.
 */
class EditionComparer
{
    public const STRATEGY_EVERYTHING = 'everything';

    public const STRATEGY_CHANGED = 'changed';

    public const STRATEGY_GAPS = 'gaps';

    public const STRATEGIES = [
        self::STRATEGY_EVERYTHING,
        self::STRATEGY_CHANGED,
        self::STRATEGY_GAPS,
    ];

    /** @var array<int, array<string, mixed>> */
    private array $changes = [];

    /** @var array<int, string> */
    private array $skipped = [];

    /**
     * @param  array<string, mixed>  $payload  the extracted edition
     */
    public function compare(
        BiicfEdition $edition,
        array $payload,
        string $strategy = self::STRATEGY_CHANGED
    ): int {
        $this->changes = [];
        $this->skipped = [];

        $seen = [];

        foreach ($payload['job_roles'] ?? [] as $role) {
            $title = $role['title'] ?? null;

            if ($title === null) {
                continue;
            }

            /*
             * The book prints Chief Technology Officer twice,
             * once under Telecommunications and Network and
             * again under Applications and Solutions Development,
             * with different duties under each. The framework
             * holds one role per title, so the second printing
             * has nowhere to go: compared as well, it collides
             * with the first on every field it touches.
             *
             * The first is compared and the second is reported
             * rather than dropped quietly, because an edition
             * that silently ignores a page of the book is worse
             * than one that says it could not use it.
             */
            if (isset($seen[$title])) {
                $this->skipped[] = sprintf(
                    'The book prints %s twice — under %s and again under %s. The '
                    .'framework holds one role per title, so only the first was '
                    .'compared. Enter the second by hand if it should be a role '
                    .'of its own.',
                    $title,
                    $seen[$title],
                    $role['sub_sector'] ?? 'another sub-sector'
                );

                continue;
            }

            $seen[$title] = $role['sub_sector'] ?? 'an unnamed sub-sector';

            $this->compareRole($role, $strategy);
        }

        foreach ($payload['competencies'] ?? [] as $competency) {
            $this->compareCompetency($competency, $strategy);
        }

        $this->flagRemovedRoles($payload['job_roles'] ?? [], $strategy);

        return $this->write($edition);
    }

    /**
     * What the edition held that the framework had no place for.
     *
     * @return array<int, string>
     */
    public function skipped(): array
    {
        return $this->skipped;
    }

    /**
     * @param  array<string, mixed>  $role
     */
    private function compareRole(array $role, string $strategy): void
    {
        $existing = BiicfJobRole::where('title', $role['title'])->first();

        $subject = [
            'subject_type' => 'job_role',
            'subject_key' => $role['title'],
            'subject_label' => $role['title'],
        ];

        if ($existing === null) {
            /*
             * A role the framework did not have before. Proposed
             * whole rather than field by field: there is nothing
             * to compare it against, and reviewing forty fields
             * one at a time to add one role would be its own
             * kind of unusable.
             */
            $this->add($subject + [
                'field' => '*',
                'kind' => BiicfEditionChange::KIND_ADDED,
                'current_value' => null,
                'proposed_value' => $role,
            ], $strategy, hasCurrent: false);

            return;
        }

        /*
         * Only the fields the edition actually states.
         *
         * A reader that could not make sense of something leaves
         * it out rather than proposing a guess, and the book
         * itself leaves fields empty — IT Technician is printed
         * with no recommended courses at all. A field that is not
         * there is not a field saying "nothing": treated as one,
         * it proposes emptying what the framework holds, and an
         * administrator working down a long list could approve
         * that without ever seeing what it really was.
         */
        if (array_key_exists('sub_sector', $role)) {
            $this->field($subject, 'sub_sector', $existing->subSector?->name, $role['sub_sector'], $strategy);
        }

        if (array_key_exists('alternate_titles', $role)) {
            $this->field($subject, 'alternative_titles', $existing->alternative_titles ?? [], $role['alternate_titles'], $strategy);
        }

        if (array_key_exists('description', $role)) {
            $this->field(
                $subject,
                'description',
                $existing->descriptionPoints->pluck('text')->all(),
                $role['description'],
                $strategy
            );
        }

        /*
         * As a set, which is what a role's courses are: the
         * pivot cannot hold one twice, and the book repeats a
         * course on two of its role pages. Sorted, because the
         * order courses are listed in is not something the
         * framework states.
         */
        if (array_key_exists('trainings', $role)) {
            $this->field(
                $subject,
                'trainings',
                $existing->trainings->pluck('name')->unique()->sort()->values()->all(),
                collect($role['trainings'])->unique()->sort()->values()->all(),
                $strategy
            );
        }

        if (array_key_exists('tiers', $role)) {
            $this->field(
                $subject,
                'tiers',
                $existing->tiers->pluck('label')->all(),
                $role['tiers'],
                $strategy
            );
        }

        if (array_key_exists('entry_requirements', $role)) {
            $this->compareRequirements($subject, $existing, $role, $strategy);
        }

        if (array_key_exists('competencies', $role)) {
            $this->compareTable($subject, $existing, $role, $strategy);
        }
    }

    /**
     * @param  array<string, string>  $subject
     * @param  array<string, mixed>  $role
     */
    private function compareRequirements(
        array $subject,
        BiicfJobRole $existing,
        array $role,
        string $strategy
    ): void {
        foreach ($role['entry_requirements'] ?? [] as $label => $alternatives) {
            $tier = $existing->tiers->firstWhere('label', $label);

            $current = $tier
                ? $tier->entryRequirements->pluck('raw_text')->all()
                : null;

            $this->field(
                $subject,
                "entry_requirements.{$label}",
                $current,
                collect($alternatives)->pluck('raw_text')->all(),
                $strategy
            );
        }
    }

    /**
     * The competency table, one tier's proficiency at a time.
     *
     * Per cell rather than per table: an edition that raises one
     * competency by a rung should read as that, not as the whole
     * table having been replaced.
     *
     * @param  array<string, string>  $subject
     * @param  array<string, mixed>  $role
     */
    private function compareTable(
        array $subject,
        BiicfJobRole $existing,
        array $role,
        string $strategy
    ): void {
        $levels = BiicfProficiencyLevel::all()->keyBy('id');

        $current = [];

        /*
         * Indexed by what two spellings of a name have in
         * common, so that the book's spelling finds the
         * framework's.
         *
         * They differ constantly: "Creativity & Innovation" for
         * "Creativity and Innovation", "Systems Integration" for
         * "System Integration", "Decision-making/ Judgement" for
         * "Decision-Making". Matched literally, each is a
         * competency added and another removed — two entries to
         * think about, over an ampersand — and where the two
         * differ only in case, which the table's unique index
         * does not, the pair collide on insert and the whole
         * edition fails.
         *
         * {@see CompetencyName} decides what counts as the same.
         */
        $named = [];

        foreach ($existing->tiers as $tier) {
            foreach ($tier->competencies as $competency) {
                $level = $levels->get($competency->pivot->proficiency_level_id);

                $current[$competency->name][$tier->label] = $level?->level_number;
                $named[CompetencyName::key($competency->name)] = $competency->name;
            }
        }

        $matched = [];

        foreach ($role['competencies'] ?? [] as $row) {
            /*
             * Named as the framework names it where it knows it,
             * so the field a difference is recorded against stays
             * the same between editions.
             */
            $name = $named[CompetencyName::key($row['name'])] ?? $row['name'];

            $matched[$name] = true;

            foreach ($row['levels'] as $label => $number) {
                $this->field(
                    $subject,
                    "competencies.{$name}.{$label}",
                    $current[$name][$label] ?? null,
                    $number,
                    $strategy
                );
            }
        }

        /*
         * A competency the role no longer requires. Proposed as
         * a removal rather than left in place: an edition
         * dropping a requirement is a change a student should
         * see, once an administrator agrees it.
         */
        foreach ($current as $name => $byTier) {
            if (isset($matched[$name])) {
                continue;
            }

            foreach ($byTier as $label => $number) {
                $this->add($subject + [
                    'field' => "competencies.{$name}.{$label}",
                    'kind' => BiicfEditionChange::KIND_REMOVED,
                    'current_value' => $number,
                    'proposed_value' => null,
                ], $strategy, hasCurrent: true);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $competency
     */
    private function compareCompetency(array $competency, string $strategy): void
    {
        $existing = BiicfCompetency::where('name', $competency['name'])
            ->where('type', $competency['type'])
            ->first();

        $subject = [
            'subject_type' => 'competency',
            'subject_key' => $competency['name'].'|'.$competency['type'],
            'subject_label' => $competency['name'],
        ];

        if ($existing === null) {
            $this->add($subject + [
                'field' => '*',
                'kind' => BiicfEditionChange::KIND_ADDED,
                'current_value' => null,
                'proposed_value' => $competency,
            ], $strategy, hasCurrent: false);

            return;
        }

        $this->field(
            $subject,
            'definition',
            $existing->description,
            $competency['definition'],
            $strategy
        );

        foreach ($competency['descriptors'] ?? [] as $set) {
            foreach ($set['levels'] as $level) {
                if ($level['statements'] === []) {
                    continue;
                }

                $descriptor = $existing->levelDescriptors
                    ->first(fn ($row) => $row->proficiencyLevel?->level_number === $level['level_number']
                        && (string) $row->scope_label === (string) $set['scope']);

                $scope = $set['scope'] ?: 'general';

                $this->field(
                    $subject,
                    "descriptors.{$scope}.{$level['level_number']}",
                    $descriptor?->statements,
                    $level['statements'],
                    $strategy
                );
            }
        }
    }

    /**
     * Roles the framework holds that the new edition does not.
     *
     * Flagged rather than deleted even once accepted: a student's
     * saved recommendation points at the role, and the framework
     * retires a role rather than erasing it.
     *
     * @param  array<int, array<string, mixed>>  $roles
     */
    private function flagRemovedRoles(array $roles, string $strategy): void
    {
        if ($roles === []) {
            return;
        }

        $titles = collect($roles)->pluck('title')->all();

        BiicfJobRole::query()
            ->whereNotIn('title', $titles)
            ->get()
            ->each(fn (BiicfJobRole $role) => $this->add([
                'subject_type' => 'job_role',
                'subject_key' => $role->title,
                'subject_label' => $role->title,
                'field' => '*',
                'kind' => BiicfEditionChange::KIND_REMOVED,
                'current_value' => ['title' => $role->title],
                'proposed_value' => null,
            ], $strategy, hasCurrent: true));
    }

    /**
     * Record one field's difference, if the strategy wants it.
     *
     * @param  array<string, string>  $subject
     */
    private function field(
        array $subject,
        string $field,
        mixed $current,
        mixed $proposed,
        string $strategy
    ): void {
        $hasCurrent = ! $this->isEmpty($current);

        if ($this->same($current, $proposed) && $strategy !== self::STRATEGY_EVERYTHING) {
            return;
        }

        $this->add($subject + [
            'field' => $field,
            'kind' => $hasCurrent
                ? BiicfEditionChange::KIND_CHANGED
                : BiicfEditionChange::KIND_ADDED,
            'current_value' => $current,
            'proposed_value' => $proposed,
        ], $strategy, $hasCurrent);
    }

    /**
     * @param  array<string, mixed>  $change
     */
    private function add(array $change, string $strategy, bool $hasCurrent): void
    {
        /*
         * Filling gaps leaves anything already recorded alone,
         * which is what makes it safe to run over a framework an
         * administrator has been correcting by hand.
         */
        if ($strategy === self::STRATEGY_GAPS && $hasCurrent) {
            return;
        }

        if ($strategy === self::STRATEGY_GAPS
            && $change['kind'] === BiicfEditionChange::KIND_REMOVED) {
            return;
        }

        $this->changes[] = $change;
    }

    private function same(mixed $current, mixed $proposed): bool
    {
        if (is_array($current) && is_array($proposed)) {
            return $current == $proposed;
        }

        /*
         * Loose on purpose for scalars: a proficiency read back
         * from the database is a string where the book's is an
         * integer, and "4" differing from 4 is not a change an
         * administrator should be asked about.
         */
        return (string) $current === (string) $proposed;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === [] || $value === '';
    }

    /**
     * Write the comparison out, replacing any earlier one.
     *
     * Decisions already taken on this edition are kept: an
     * administrator part way through a review should not lose
     * their work because the extraction was run again.
     */
    private function write(BiicfEdition $edition): int
    {
        $decided = $edition->changes()
            ->where('decision', '!=', BiicfEditionChange::DECISION_PENDING)
            ->get()
            ->keyBy(fn (BiicfEditionChange $change) => $change->subject_type
                .'|'.$change->subject_key
                .'|'.$change->field);

        $edition->changes()->pending()->delete();

        /*
         * Two differences that the database would consider one.
         *
         * A change is identified by its subject and field, and
         * the index enforcing that does not distinguish case. So
         * two fields differing only in a capital letter are one
         * row as far as the table is concerned, and writing both
         * fails the whole edition rather than the one row.
         *
         * The first is kept and the second reported. It is a
         * difference going unreviewed either way; this way it
         * is one somebody has been told about.
         */
        $written = [];

        foreach ($this->changes as $change) {
            $key = $change['subject_type'].'|'.$change['subject_key'].'|'.$change['field'];

            if ($decided->has($key)) {
                continue;
            }

            $folded = mb_strtolower($key);

            if (isset($written[$folded])) {
                if ($written[$folded] !== $change['field']) {
                    $this->skipped[] = sprintf(
                        '%s has two differences the review cannot tell apart — "%s" '
                        .'and "%s" differ only in case. The first was kept.',
                        $change['subject_label'] ?? $change['subject_key'],
                        $written[$folded],
                        $change['field']
                    );
                }

                continue;
            }

            $written[$folded] = $change['field'];

            $edition->changes()->create($change);
        }

        return $edition->changes()->count();
    }
}
