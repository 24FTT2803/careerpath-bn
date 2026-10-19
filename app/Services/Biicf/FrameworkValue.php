<?php

namespace App\Services\Biicf;

use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use Illuminate\Support\Collection;

/**
 * What the framework currently holds for one field of one role.
 *
 * The field is named the way the reader and the comparison name
 * it — "description", "entry_requirements.Specialist",
 * "competencies.Cloud Computing" — so a place in the book and
 * the value to check against it are found by the same key.
 *
 * Read from the live tables rather than from an edition's
 * snapshot: the question being asked is whether what students
 * see right now matches the book.
 */
class FrameworkValue
{
    /** @var Collection<int, BiicfProficiencyLevel>|null */
    private $levels = null;

    /**
     * @return array{label: string, value: array<int, string>, missing: bool}
     */
    public function for(BiicfJobRole $role, string $field): array
    {
        [$head, $rest] = array_pad(explode('.', $field, 2), 2, null);

        $value = match ($head) {
            '*' => [$role->title],
            'sub_sector' => [$role->subSector?->name],
            'alternate_titles', 'alternative_titles' => (array) ($role->alternative_titles ?? []),
            'job_family' => $role->jobFamilies->pluck('name')->all(),
            'functional_group' => [$role->functional_group],
            'job_level', 'tiers' => $role->tiers->pluck('label')->all(),
            'description' => $role->descriptionPoints->pluck('text')->all(),
            'trainings' => $role->trainings->pluck('name')->all(),
            'programming_languages' => $role->programmingLanguages->pluck('name')->all(),
            'critical_work_functions' => $this->workFunctions($role),
            'entry_requirements' => $this->requirements($role, $rest),
            'competencies' => $this->competency($role, $rest),
            default => [],
        };

        $value = array_values(array_filter(
            array_map(fn ($line) => is_string($line) ? trim($line) : $line, $value),
            fn ($line) => $line !== null && $line !== ''
        ));

        return [
            'label' => $this->label($field),
            'value' => $value,
            'missing' => $value === [],
        ];
    }

    /**
     * The field, said in words.
     *
     * A path reads as a path — "competencies.Cloud Computing" —
     * and an administrator checking a page against a book is
     * reading, not debugging.
     */
    public function label(string $field): string
    {
        [$head, $rest] = array_pad(explode('.', $field, 2), 2, null);

        $named = [
            '*' => 'The whole role',
            'sub_sector' => 'Sub-sector',
            'alternate_titles' => 'Alternate job titles',
            'alternative_titles' => 'Alternate job titles',
            'job_family' => 'Job family',
            'functional_group' => 'Functional group',
            'job_level' => 'Job level',
            'tiers' => 'Levels it is entered at',
            'description' => 'Job description',
            'trainings' => 'Recommended courses',
            'programming_languages' => 'Programming languages',
            'critical_work_functions' => 'Critical work functions',
        ];

        if (isset($named[$field])) {
            return $named[$field];
        }

        return match ($head) {
            'entry_requirements' => $rest === null
                ? 'Entry requirements'
                : "Entry requirements · {$rest}",
            'competencies' => "Competency · {$rest}",
            default => $named[$head] ?? str_replace('_', ' ', ucfirst($field)),
        };
    }

    /**
     * @return array<int, string>
     */
    private function workFunctions(BiicfJobRole $role): array
    {
        $lines = [];

        foreach ($role->criticalWorkFunctions->whereNull('parent_id')->sortBy('sort_order') as $top) {
            $lines[] = $top->title;

            foreach ($role->criticalWorkFunctions->where('parent_id', $top->id)->sortBy('sort_order') as $child) {
                $lines[] = '— '.$child->title;
            }
        }

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function requirements(BiicfJobRole $role, ?string $tierLabel): array
    {
        $tiers = $tierLabel === null
            ? $role->tiers
            : $role->tiers->where('label', $tierLabel);

        $lines = [];

        foreach ($tiers as $tier) {
            foreach ($tier->entryRequirements as $requirement) {
                $lines[] = $requirement->raw_text;
            }
        }

        return $lines;
    }

    /**
     * One competency's proficiency, per tier.
     *
     * Both scales are numbered from one and they are not the
     * same length, so the rung is given as a name and a
     * proportion rather than a bare figure.
     *
     * @return array<int, string>
     */
    private function competency(BiicfJobRole $role, ?string $name): array
    {
        if ($name === null) {
            return [];
        }

        $this->levels ??= BiicfProficiencyLevel::all()->keyBy('id');

        $lines = [];

        foreach ($role->tiers as $tier) {
            $match = $tier->competencies->first(
                fn ($competency) => CompetencyName::key($competency->name) === CompetencyName::key($name)
            );

            if ($match === null) {
                continue;
            }

            $level = $this->levels->get($match->pivot->proficiency_level_id);

            $lines[] = $level === null
                ? "{$tier->label}: no proficiency recorded"
                : sprintf(
                    '%s: %s (%d of %d)',
                    $tier->label,
                    $level->name,
                    $level->level_number,
                    $level->scaleLength()
                );
        }

        return $lines;
    }
}
