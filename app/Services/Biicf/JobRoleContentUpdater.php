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
