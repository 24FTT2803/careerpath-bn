<?php

namespace App\Services\Biicf;

use App\Models\BiicfCompetency;
use App\Models\BiicfCompetencyLevelDescriptor;

/**
 * What a competency means at each of its levels, as typed.
 *
 * The admin form held a competency's name, type and definition
 * and nothing else, so the part a student actually reads — what
 * Cloud Computing at Apply asks of someone — could be imported
 * and never corrected.
 *
 * Two rules the form has to keep:
 *
 * A level with nothing written at it has no row, because that is
 * how the book's N/A is recorded. Clearing a level therefore
 * deletes its row rather than storing an empty list, and a level
 * that never had one is left alone.
 *
 * A descriptor belongs to a scope. Four competencies are
 * described differently depending on the role, so the same level
 * can carry more than one set of statements, and writing a set
 * must not overwrite its neighbour.
 */
class CompetencyDescriptorUpdater
{
    /**
     * @param  array<int|string, array<string, mixed>>  $sets  each
     *                                                         ['scope' => ?string, 'levels' => [proficiency_level_id => ?string]]
     * @return int how many levels are described after the write
     */
    public function write(BiicfCompetency $competency, array $sets): int
    {
        foreach ($sets as $set) {
            $scope = trim((string) ($set['scope'] ?? ''));

            foreach ($set['levels'] ?? [] as $levelId => $text) {
                $this->level($competency, (int) $levelId, $scope, $this->lines($text));
            }
        }

        return $competency->levelDescriptors()->count();
    }

    /**
     * @param  array<int, string>  $statements
     */
    private function level(
        BiicfCompetency $competency,
        int $levelId,
        string $scope,
        array $statements
    ): void {
        $existing = BiicfCompetencyLevelDescriptor::query()
            ->where('competency_id', $competency->id)
            ->where('proficiency_level_id', $levelId)
            ->where('scope_label', $scope)
            ->first();

        if ($statements === []) {
            $existing?->delete();

            return;
        }

        if ($existing !== null) {
            $existing->update(['statements' => $statements]);

            return;
        }

        BiicfCompetencyLevelDescriptor::create([
            'competency_id' => $competency->id,
            'proficiency_level_id' => $levelId,
            'scope_label' => $scope,
            'statements' => $statements,
        ]);
    }

    /**
     * One statement per line, as the book sets them.
     *
     * @return array<int, string>
     */
    private function lines(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $value) ?: []),
            fn (string $line) => $line !== ''
        ));
    }
}
