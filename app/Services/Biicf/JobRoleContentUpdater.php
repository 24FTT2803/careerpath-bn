<?php

namespace App\Services\Biicf;

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobDescriptionPoint;
use App\Models\BiicfJobRole;

/**
 * Turns the admin form's plain textareas into the structured
 * rows a role's description and duties are now stored as.
 *
 * The form stays a textarea on purpose — one line per point is
 * what an administrator has always typed here, and nothing is
 * gained by making them build a tree by hand. What the book's
 * own structure adds, family-specific and nested duties, comes
 * from the edition import rather than from this form.
 */
class JobRoleContentUpdater
{
    /**
     * Replace a role's description with one point per line.
     */
    public function replaceDescription(BiicfJobRole $role, ?string $text): void
    {
        $role->descriptionPoints()->delete();

        foreach ($this->lines($text) as $index => $line) {
            BiicfJobDescriptionPoint::create([
                'job_role_id' => $role->id,
                'text' => $line,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Replace the languages a role is expected to know, one per
     * line.
     *
     * A section of the book in its own right, kept apart from
     * the recommended courses because it is not one: the
     * framework names no training for a language, it expects the
     * role to have it.
     */
    public function replaceProgrammingLanguages(BiicfJobRole $role, ?string $text): void
    {
        (new ProgrammingLanguageWriter)->write($role, $this->lines($text));
    }

    /**
     * Replace a role's generic duties with one per line.
     *
     * Only the generic ones. A role's family-specific duties
     * come from the reference book, carry a family and sometimes
     * sub-duties, and none of that is expressible in this
     * textarea — so editing it must not quietly delete them.
     */
    public function replaceGenericWorkFunctions(BiicfJobRole $role, ?string $text): void
    {
        $role->criticalWorkFunctions()
            ->generic()
            ->delete();

        foreach ($this->lines($text) as $index => $line) {
            BiicfCriticalWorkFunction::create([
                'job_role_id' => $role->id,
                'kind' => BiicfCriticalWorkFunction::KIND_GENERIC,
                'text' => $line,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Replace a role's duties with the tree the form drew.
     *
     * The whole tree, generic and family-specific, at every level
     * of nesting — which is the point. The textarea it replaces
     * could only see the generic ones, so deleting a duplicate
     * there left its copies standing wherever they were nested,
     * and the data said one thing while the form said another.
     *
     * The legacy column is written alongside it, flattened, so
     * nothing still reading that column disagrees with the rows.
     *
     * @param  array<int, array<string, mixed>>  $tree
     */
    public function replaceWorkFunctions(BiicfJobRole $role, array $tree): void
    {
        $tree = $this->pruned($tree);

        (new WorkFunctionWriter)->write($role, $tree);

        $role->update([
            'critical_work_function' => collect($tree)
                ->pluck('text')
                ->implode("\n"),
        ]);
    }

    /**
     * The tree with its empty nodes dropped and its depth
     * capped.
     *
     * A node with nothing typed in it is a row the form left
     * open, not a duty, and it takes its children with it when
     * it goes — an orphaned sub-duty reads as a duty of the
     * role.
     *
     * Three levels, because that is as deep as the book goes: a
     * heading, its duties, and sub-duties under some of them.
     *
     * @param  array<int, array<string, mixed>>  $tree
     * @return array<int, array<string, mixed>>
     */
    private function pruned(array $tree, int $depth = 1): array
    {
        $kept = [];

        foreach ($tree as $node) {
            $text = trim((string) ($node['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $children = $depth < 3
                ? $this->pruned($node['children'] ?? [], $depth + 1)
                : [];

            $kept[] = [
                'text' => $text,
                'kind' => $node['kind'] ?? BiicfCriticalWorkFunction::KIND_GENERIC,
                'job_family_id' => $node['job_family_id'] ?? null,
                'children' => $children,
            ];
        }

        return $kept;
    }

    /**
     * The non-empty lines of a textarea's value.
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
