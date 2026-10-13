<?php

namespace App\Services\Biicf;

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobRole;

/**
 * Writing a role's duties, keeping the book's nesting.
 *
 * Lives on its own because more than one thing needs to do it,
 * and two copies of a recursive write that must agree is a bug
 * waiting for the day they stop agreeing.
 *
 * Always replaces. A role's duties are a complete statement of
 * what it does, not a list to be added to, and writing without
 * clearing is how one role came to list "Network Operations and
 * Incidents Management" three times over — each copy a reading
 * from a different day, none of them wrong, all of them there
 * at once.
 *
 * A duty is either generic to the role or specific to one of its
 * job families, and only the top of a list says which: what
 * nests under a family-specific heading belongs to the same
 * family, and saying so again on every child is how the two come
 * to disagree. So children inherit both.
 */
class WorkFunctionWriter
{
    /**
     * @param  array<int, array{text: string, kind?: string, job_family_id?: int|null, children?: array<int, mixed>}>  $functions
     * @return int how many rows were written
     */
    public function write(BiicfJobRole $role, array $functions): int
    {
        $role->criticalWorkFunctions()->delete();

        return $this->put($role, $functions);
    }

    /**
     * @param  array<int, array<string, mixed>>  $functions
     */
    private function put(
        BiicfJobRole $role,
        array $functions,
        ?int $parentId = null,
        ?string $kind = null,
        ?int $familyId = null
    ): int {
        $written = 0;

        foreach ($functions as $index => $function) {
            /*
             * At the top of a list, what the node says. Beneath
             * it, what its parent said — a sub-duty of a
             * family's duty is that family's too.
             */
            $thisKind = $parentId === null
                ? $this->kindOf($function)
                : $kind;

            $thisFamily = $parentId === null
                ? $this->familyOf($function, $thisKind)
                : $familyId;

            $row = BiicfCriticalWorkFunction::create([
                'job_role_id' => $role->id,
                'kind' => $thisKind,
                'job_family_id' => $thisFamily,
                'parent_id' => $parentId,
                'text' => $function['text'],
                'sort_order' => $index + 1,
            ]);

            $written++;

            if (($function['children'] ?? []) !== []) {
                $written += $this->put(
                    $role,
                    $function['children'],
                    $row->id,
                    $thisKind,
                    $thisFamily
                );
            }
        }

        return $written;
    }

    /**
     * @param  array<string, mixed>  $function
     */
    private function kindOf(array $function): string
    {
        return ($function['kind'] ?? null) === BiicfCriticalWorkFunction::KIND_SPECIFIC
            ? BiicfCriticalWorkFunction::KIND_SPECIFIC
            : BiicfCriticalWorkFunction::KIND_GENERIC;
    }

    /**
     * A family only means something on a specific duty.
     *
     * A generic one belongs to the role however it is practised,
     * so a family left set on it after a change of kind would
     * show the duty under a heading it does not belong to.
     *
     * @param  array<string, mixed>  $function
     */
    private function familyOf(array $function, string $kind): ?int
    {
        if ($kind !== BiicfCriticalWorkFunction::KIND_SPECIFIC) {
            return null;
        }

        $family = $function['job_family_id'] ?? null;

        return $family === null || $family === '' ? null : (int) $family;
    }
}
