<?php

namespace App\Services\Biicf;

use App\Models\BiicfJobRole;
use App\Models\BiicfProficiencyLevel;
use Illuminate\Support\Collection;

/**
 * The framework as it currently stands, in the shape an
 * extracted edition arrives in.
 *
 * Written onto the outgoing edition when a new one is applied,
 * so what a student was shown last year stays readable after
 * the live tables have moved on. Reading it back in the same
 * shape an upload uses means an old edition can be compared
 * against, or restored, by the same code that loads a new one.
 */
class FrameworkSnapshot
{
    /**
     * @return array<string, mixed>
     */
    public function capture(): array
    {
        $levels = BiicfProficiencyLevel::all()->keyBy('id');

        $roles = BiicfJobRole::query()
            ->with([
                'subSector',
                'descriptionPoints',
                'trainings',
                'tiers.entryRequirements',
                'tiers.competencies',
                'genericWorkFunctions.children.children',
            ])
            ->get()
            ->map(fn (BiicfJobRole $role) => [
                'title' => $role->title,
                'sub_sector' => $role->subSector?->name,
                'alternate_titles' => $role->alternative_titles ?? [],
                'description' => $role->descriptionPoints->pluck('text')->all(),
                'trainings' => $role->trainings->pluck('name')->all(),
                'tiers' => $role->tiers->pluck('label')->all(),
                'critical_work_functions' => $this->functions($role),
                'entry_requirements' => $role->tiers
                    ->mapWithKeys(fn ($tier) => [
                        $tier->label => $tier->entryRequirements
                            ->map(fn ($requirement) => [
                                'raw_text' => $requirement->raw_text,
                                'bdqf_level' => $requirement->bdqf_level,
                                'field_of_study' => $requirement->field_of_study,
                                'years_experience' => $requirement->years_experience,
                                'requires_industry_certification' => $requirement->requires_industry_certification,
                                'requires_industry_experience' => $requirement->requires_industry_experience,
                            ])
                            ->all(),
                    ])
                    ->all(),
                'competencies' => $this->competencies($role, $levels),
            ])
            ->all();

        return [
            'captured_at' => now()->toIso8601String(),
            'job_roles' => $roles,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function functions(BiicfJobRole $role): array
    {
        $walk = function ($functions) use (&$walk) {
            return $functions->map(fn ($function) => [
                'text' => $function->text,
                'children' => $walk($function->children),
            ])->all();
        };

        return $walk($role->genericWorkFunctions);
    }

    /**
     * @param  Collection<int, BiicfProficiencyLevel>  $levels
     * @return array<int, array<string, mixed>>
     */
    private function competencies(BiicfJobRole $role, $levels): array
    {
        $out = [];

        foreach ($role->tiers as $tier) {
            foreach ($tier->competencies as $competency) {
                $key = $competency->name.'|'.$competency->type;

                $out[$key] ??= [
                    'name' => $competency->name,
                    'type' => $competency->type,
                    'levels' => [],
                ];

                $out[$key]['levels'][$tier->label] = $levels
                    ->get($competency->pivot->proficiency_level_id)
                    ?->level_number;
            }
        }

        return array_values($out);
    }
}
