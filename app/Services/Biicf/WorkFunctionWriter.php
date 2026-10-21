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
        $this->forget($role);

        return $this->put($role, $functions);
    }

    /**
     * Clear a role's duties, deepest first.
     *
     * One statement would be simpler and is what this used to do.
     * It does not work: parent_id is a foreign key onto this same
     * table with ON DELETE CASCADE, and InnoDB does not apply a
     * cascade reliably when the parent and the child are both in
     * the reach of one multi-row DELETE. Some rows go, some stay.
     *
     * That is how three old readings of the book survived three
     * rewrites — every row at sort_order 1 and its sub-duties
     * went, and everything else was left behind, which is a
     * database that disagrees with itself in a way nothing on
     * screen can explain.
     *
     * SQLite cascades it properly, so the tests were green
     * throughout.
     *
     * Deleting the leaves first means no delete ever cascades:
     * by the time a parent goes, it has no children left to take
     * with it.
     *
     * @param  string|null  $kind  only duties of this kind, or all of them
     * @return int how many rows were cleared
     */
    public function forget(BiicfJobRole $role, ?string $kind = null): int
    {
        $gone = 0;

        while (true) {
            $rows = BiicfCriticalWorkFunction::query()
                ->where('job_role_id', $role->id)
                ->when($kind !== null, fn ($query) => $query->where('kind', $kind))
                ->get(['id', 'parent_id']);

            if ($rows->isEmpty()) {
                return $gone;
            }

            $parents = $rows->pluck('parent_id')->filter()->unique()->all();

            $leaves = $rows
                ->reject(fn ($row) => in_array($row->id, $parents, true))
                ->pluck('id');

            /*
             * Every remaining row is some other row's parent,
             * which a tree cannot be. Rather than loop for ever,
             * take what is left in one go — the cascade can do no
             * harm once nothing else is waiting to be deleted.
             */
            if ($leaves->isEmpty()) {
                return $gone + BiicfCriticalWorkFunction::whereIn('id', $rows->pluck('id'))->delete();
            }

            $gone += BiicfCriticalWorkFunction::whereIn('id', $leaves)->delete();
        }
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
