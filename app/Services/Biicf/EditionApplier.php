<?php

namespace App\Services\Biicf;

use App\Models\BiicfCompetency;
use App\Models\BiicfEdition;
use App\Models\BiicfEditionChange;
use App\Models\BiicfEntryRequirement;
use App\Models\BiicfJobDescriptionPoint;
use App\Models\BiicfJobRole;
use App\Models\BiicfJobRoleTier;
use App\Models\BiicfProficiencyLevel;
use App\Models\BiicfSubSector;
use App\Models\BiicfTraining;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Write an edition's accepted changes to the live tables.
 *
 * Nothing here decides anything: every change has already been
 * accepted, kept or edited by an administrator, and this walks
 * the decisions. A kept change writes nothing and is still
 * marked applied — the decision was made, and the framework
 * agrees with it already.
 *
 * Applied in one transaction. Half an edition is worse than
 * none: a student reading a role part way through would see its
 * duties from the new book against its competencies from the
 * old one.
 */
class EditionApplier
{
    public function __construct(
        private readonly FrameworkSnapshot $snapshot = new FrameworkSnapshot,
    ) {}

    public function apply(BiicfEdition $edition, ?User $by = null): int
    {
        if (! $edition->isReviewed()) {
            throw new RuntimeException(
                'This edition still has changes nobody has decided. '
                .'Every difference is reviewed before any of it is applied.'
            );
        }

        if ($edition->applied_at !== null) {
            throw new RuntimeException('This edition has already been applied.');
        }

        return DB::transaction(function () use ($edition, $by) {
            $this->keepOutgoing($edition);

            $written = 0;

            foreach ($edition->changes()->unapplied()->get() as $change) {
                if ($change->writes()) {
                    $this->write($change, $edition);
                    $written++;
                }

                $change->update(['applied_at' => now()]);
            }

            BiicfEdition::where('id', '!=', $edition->id)
                ->update(['is_current' => false]);

            $edition->update([
                'is_current' => true,
                'applied_at' => now(),
                'notes' => $by
                    ? "Applied by {$by->name}."
                    : $edition->notes,
            ]);

            return $written;
        });
    }

    /**
     * Keep the framework the outgoing edition described.
     *
     * Written onto that edition rather than thrown away, so a
     * report generated against it can still say what it was
     * generated against.
     */
    private function keepOutgoing(BiicfEdition $edition): void
    {
        $outgoing = BiicfEdition::where('is_current', true)
            ->where('id', '!=', $edition->id)
            ->first();

        if ($outgoing === null) {
            return;
        }

        $outgoing->update(['snapshot_data' => $this->snapshot->capture()]);
    }

    private function write(BiicfEditionChange $change, BiicfEdition $edition): void
    {
        match ($change->subject_type) {
            'job_role' => $this->writeJobRole($change, $edition),
            'competency' => $this->writeCompetency($change, $edition),
            default => throw new RuntimeException(
                "Nothing knows how to apply a {$change->subject_type} change."
            ),
        };
    }

    private function writeJobRole(BiicfEditionChange $change, BiicfEdition $edition): void
    {
        if ($change->kind === BiicfEditionChange::KIND_REMOVED && $change->field === '*') {
            $this->retire($change->subject_key, $edition);

            return;
        }

        $role = BiicfJobRole::where('title', $change->subject_key)->first();

        if ($role === null) {
            $role = $this->createRole($change->outcome(), $edition);

            if ($change->field === '*') {
                return;
            }
        }

        $value = $change->outcome();
        $field = $change->field;

        match (true) {
            $field === 'sub_sector' => $this->setSubSector($role, $value),
            $field === 'alternative_titles' => $role->update(['alternative_titles' => $value]),
            $field === 'description' => $this->setDescription($role, $value ?? []),
            $field === 'trainings' => $this->setTrainings($role, $value ?? []),
            $field === 'tiers' => $this->setTiers($role, $value ?? []),
            str_starts_with($field, 'entry_requirements.') => $this->setRequirements(
                $role,
                Str::after($field, 'entry_requirements.'),
                $value ?? []
            ),
            str_starts_with($field, 'competencies.') => $this->setProficiency(
                $role,
                $field,
                $value
            ),
            default => throw new RuntimeException(
                "Nothing knows how to apply a job role's {$field}."
            ),
        };

        $role->update([
            'source_edition_id' => $edition->id,
            'origin' => 'imported',
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function createRole(?array $payload, BiicfEdition $edition): BiicfJobRole
    {
        if ($payload === null || ! isset($payload['title'])) {
            throw new RuntimeException(
                'A new job role was accepted without the role to create.'
            );
        }

        $subSector = BiicfSubSector::firstOrCreate(
            ['slug' => Str::slug(str_replace(['/', '&'], '-', $payload['sub_sector']))],
            ['name' => $payload['sub_sector']]
        );

        $role = BiicfJobRole::create([
            'sub_sector_id' => $subSector->id,
            'title' => $payload['title'],
            'slug' => Str::slug(str_replace(['/', '&'], '-', $payload['title'])),
            'alternative_titles' => $payload['alternate_titles'] ?? [],
            'source_edition_id' => $edition->id,
            'origin' => 'imported',
        ]);

        $this->setTiers($role, $payload['tiers'] ?? ['Entrant']);
        $this->setDescription($role, $payload['description'] ?? []);
        $this->setTrainings($role, $payload['trainings'] ?? []);

        foreach ($payload['entry_requirements'] ?? [] as $label => $alternatives) {
            $this->setRequirements(
                $role,
                $label,
                collect($alternatives)->pluck('raw_text')->all()
            );
        }

        foreach ($payload['competencies'] ?? [] as $row) {
            foreach ($row['levels'] as $label => $number) {
                $this->setProficiency(
                    $role,
                    "competencies.{$row['name']}.{$label}",
                    $number
                );
            }
        }

        return $role;
    }

    /**
     * Mark a role the new edition no longer lists.
     *
     * Never deleted: a student's saved recommendation points at
     * it, and the framework retiring a role does not unmake the
     * advice someone was given while it was current.
     */
    private function retire(string $title, BiicfEdition $edition): void
    {
        BiicfJobRole::where('title', $title)
            ->update(['retired_in_edition_id' => $edition->id]);
    }

    private function setSubSector(BiicfJobRole $role, ?string $name): void
    {
        if ($name === null) {
            return;
        }

        /*
         * An existing sub-sector first, matched loosely.
         *
         * The framework has six, and the book spells two of them
         * more than one way: "Telecommunications and Network" on
         * four pages and "Telecommunications and Networks" on two.
         * Matched exactly, the second spelling made a seventh
         * sub-sector and moved two roles into it, splitting a
         * part of the framework in half over a plural — and a
         * sub-sector deletes its roles with it, so tidying that
         * up by hand is one click away from losing them.
         */
        $loose = self::looseName($name);

        $subSector = BiicfSubSector::all()
            ->first(fn (BiicfSubSector $sector) => self::looseName($sector->name) === $loose);

        if ($subSector === null) {
            $subSector = BiicfSubSector::create([
                'name' => $name,
                'slug' => Str::slug(str_replace(['/', '&'], '-', $name)),
                /*
                 * At the end, rather than at nought alongside
                 * whatever already sits there.
                 */
                'sort_order' => (int) BiicfSubSector::max('sort_order') + 1,
            ]);
        }

        $role->update(['sub_sector_id' => $subSector->id]);
    }

    /**
     * A sub-sector's name reduced to what its spellings share.
     *
     * The same idea as {@see CompetencyName}, kept separate
     * because that one carries a list of competency pairs no
     * rule would find, and none of it applies here: there are
     * six sub-sectors and the only way the book varies them is
     * a plural and a capital letter.
     */
    private static function looseName(string $name): string
    {
        $name = str_replace('&', ' and ', mb_strtolower(trim($name)));
        $name = (string) preg_replace('/[^a-z ]/u', ' ', $name);

        $words = [];

        foreach (preg_split('/\s+/u', trim($name)) ?: [] as $word) {
            if ($word !== '') {
                $words[] = rtrim($word, 's');
            }
        }

        return implode('', $words);
    }

    /**
     * @param  array<int, string>  $points
     */
    private function setDescription(BiicfJobRole $role, array $points): void
    {
        $role->descriptionPoints()->delete();

        foreach ($points as $index => $text) {
            BiicfJobDescriptionPoint::create([
                'job_role_id' => $role->id,
                'text' => $text,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @param  array<int, string>  $names
     */
    private function setTrainings(BiicfJobRole $role, array $names): void
    {
        $role->trainings()->sync(
            collect($names)
                ->unique()
                ->map(fn (string $name) => BiicfTraining::firstOrCreate(['name' => $name])->id)
                ->all()
        );
    }

    /**
     * @param  array<int, string>  $labels
     */
    private function setTiers(BiicfJobRole $role, array $labels): void
    {
        $keep = [];

        foreach ($labels as $index => $label) {
            $keep[] = BiicfJobRoleTier::updateOrCreate(
                ['job_role_id' => $role->id, 'label' => $label],
                [
                    'sort_order' => $index + 1,
                    'job_level_id' => \App\Models\BiicfJobLevel::where('name', $label)->value('id'),
                ]
            )->id;
        }

        /*
         * Through the model, so the hook clearing a tier's
         * requirements and competency rows runs.
         */
        $role->tiers()
            ->whereNotIn('id', $keep ?: [0])
            ->get()
            ->each
            ->delete();
    }

    /**
     * @param  array<int, string>  $alternatives
     */
    private function setRequirements(
        BiicfJobRole $role,
        string $label,
        array $alternatives
    ): void {
        $tier = $role->tiers()->where('label', $label)->first();

        if ($tier === null) {
            throw new RuntimeException(
                "{$role->title}: an entry requirement names a tier \"{$label}\" "
                .'the role does not have. Decide the tiers before the '
                .'requirements that hang off them.'
            );
        }

        $tier->entryRequirements()
            ->where('alternative_number', '<=', count($alternatives))
            ->delete();

        foreach ($alternatives as $index => $text) {
            BiicfEntryRequirement::create([
                'job_role_tier_id' => $tier->id,
                'alternative_number' => $index + 1,
                'raw_text' => $text,
            ]);
        }
    }

    /**
     * One cell of the role's competency table.
     *
     * The field names the competency and the tier, so a value of
     * null is the edition dropping that requirement rather than
     * leaving it unsaid.
     */
    private function setProficiency(
        BiicfJobRole $role,
        string $field,
        mixed $number
    ): void {
        [$name, $label] = $this->splitCell($field);

        $tier = $role->tiers()->where('label', $label)->first();

        if ($tier === null) {
            throw new RuntimeException(
                "{$role->title}: a proficiency names a tier \"{$label}\" "
                .'the role does not have.'
            );
        }

        $competency = BiicfCompetency::where('name', $name)->first();

        if ($competency === null) {
            throw new RuntimeException(
                "The competency library has nothing called \"{$name}\"."
            );
        }

        if ($number === null) {
            $tier->competencies()->detach($competency->id);

            return;
        }

        $level = BiicfProficiencyLevel::where('scale', $competency->scale())
            ->where('level_number', (int) $number)
            ->first();

        if ($level === null) {
            throw new RuntimeException(
                "The {$competency->scale()} scale has no level {$number}."
            );
        }

        $tier->competencies()->syncWithoutDetaching([
            $competency->id => [
                'proficiency_level_id' => $level->id,
                'is_core' => true,
            ],
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitCell(string $field): array
    {
        $rest = Str::after($field, 'competencies.');

        /*
         * The tier is the last segment: a competency's own name
         * can carry a full stop, as "Node.js" does, so splitting
         * from the left would cut one in half.
         */
        $at = strrpos($rest, '.');

        if ($at === false) {
            throw new RuntimeException("A proficiency field names no tier: {$field}.");
        }

        return [substr($rest, 0, $at), substr($rest, $at + 1)];
    }

    private function writeCompetency(BiicfEditionChange $change, BiicfEdition $edition): void
    {
        [$name, $type] = array_pad(explode('|', $change->subject_key), 2, 'technical');

        $competency = BiicfCompetency::where('name', $name)
            ->where('type', $type)
            ->first();

        if ($competency === null) {
            $payload = $change->outcome();

            $competency = BiicfCompetency::create([
                'name' => $name,
                'type' => $type,
                'slug' => Str::slug(str_replace(['/', '&'], '-', $name)),
                'description' => $payload['definition'] ?? null,
                'source_edition_id' => $edition->id,
                'origin' => 'imported',
            ]);

            if ($change->field === '*') {
                return;
            }
        }

        if ($change->field === 'definition') {
            $competency->update(['description' => $change->outcome()]);
        }

        /*
         * Level descriptors are left to a pass of their own: a
         * competency's wording at one rung is reference data the
         * seeder owns, and writing it from here would need the
         * scope and the scale resolved per statement.
         */
        $competency->update([
            'source_edition_id' => $edition->id,
            'origin' => 'imported',
        ]);
    }
}
