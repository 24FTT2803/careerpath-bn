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
 */
class WorkFunctionWriter
{
    /**
     * @param  array<int, array{text: string, children?: array<int, mixed>}>  $functions
     * @return int how many rows were written
     */
    public function write(BiicfJobRole $role, array $functions): int
    {
        $role->criticalWorkFunctions()->delete();

        return $this->put($role, $functions);
    }

    /**
     * @param  array<int, array{text: string, children?: array<int, mixed>}>  $functions
     */
    private function put(BiicfJobRole $role, array $functions, ?int $parentId = null): int
    {
        $written = 0;

        foreach ($functions as $index => $function) {
            $row = BiicfCriticalWorkFunction::create([
                'job_role_id' => $role->id,
                'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
                'parent_id' => $parentId,
                'text' => $function['text'],
                'sort_order' => $index + 1,
            ]);

            $written++;

            if (($function['children'] ?? []) !== []) {
                $written += $this->put($role, $function['children'], $row->id);
            }
        }

        return $written;
    }
}
