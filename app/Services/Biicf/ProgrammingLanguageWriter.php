<?php

namespace App\Services\Biicf;

use App\Models\BiicfJobProgrammingLanguage;
use App\Models\BiicfJobRole;

/**
 * Writing the languages a role is expected to know.
 *
 * Its own class for the same reason the duties and the ways in
 * have one: the seeder, an edition and the admin form all need
 * to do this and must do it identically.
 *
 * Replaces the list outright, which is right here and is not for
 * the ways in: a language is a name and nothing hangs off it, so
 * there is nothing an administrator can have added that is lost
 * by writing the list again. Order is the book's.
 */
class ProgrammingLanguageWriter
{
    /**
     * @param  array<int, string>  $names
     * @return int how many languages were written
     */
    public function write(BiicfJobRole $role, array $names): int
    {
        $role->programmingLanguages()->delete();

        $written = 0;

        foreach ($this->tidied($names) as $index => $name) {
            BiicfJobProgrammingLanguage::create([
                'job_role_id' => $role->id,
                'name' => $name,
                'sort_order' => $index + 1,
            ]);

            $written++;
        }

        return $written;
    }

    /**
     * Trimmed, in the order given, each one once.
     *
     * The table will not hold a language twice for one role, and
     * a list typed by hand is where a repeat comes from.
     *
     * @param  array<int, string>  $names
     * @return array<int, string>
     */
    private function tidied(array $names): array
    {
        $tidied = [];

        foreach ($names as $name) {
            $name = trim((string) $name);

            if ($name === '' || in_array($name, $tidied, true)) {
                continue;
            }

            $tidied[] = $name;
        }

        return $tidied;
    }
}
