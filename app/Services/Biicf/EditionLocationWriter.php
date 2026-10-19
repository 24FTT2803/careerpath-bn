<?php

namespace App\Services\Biicf;

use App\Models\BiicfEdition;
use App\Models\BiicfEditionLocation;
use Illuminate\Support\Facades\DB;

/**
 * Keeps where an edition's book prints each of its values.
 *
 * Written once when the book is read, and rewritten whole when
 * it is read again: the reader is the only thing that knows
 * these, and a reader corrected since the last reading should
 * correct them all rather than leave a page number from the
 * version before.
 */
class EditionLocationWriter
{
    /**
     * The reader's map, as rows.
     *
     * @param  array<string, array<string, array{page: int, spans: array<int, array<string, mixed>>}>>  $locations
     *                                                                                                              by subject key, then by field
     * @return int how many places were recorded
     */
    public function write(BiicfEdition $edition, array $locations, string $subjectType = 'job_role'): int
    {
        $rows = [];

        $now = now();

        foreach ($locations as $subject => $fields) {
            foreach ($fields as $field => $place) {
                if (! isset($place['page'], $place['spans'])) {
                    continue;
                }

                $rows[] = [
                    'edition_id' => $edition->id,
                    'subject_type' => $subjectType,
                    'subject_key' => (string) $subject,
                    'field' => (string) $field,
                    'page' => (int) $place['page'],
                    'spans' => json_encode($place['spans']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($edition, $subjectType, $rows) {
            BiicfEditionLocation::query()
                ->where('edition_id', $edition->id)
                ->where('subject_type', $subjectType)
                ->delete();

            /*
             * In chunks, because a full reading of the book
             * records some thousands of places and one insert of
             * all of them exceeds what MySQL will accept in a
             * single statement.
             */
            foreach (array_chunk($rows, 500) as $chunk) {
                BiicfEditionLocation::insert($chunk);
            }
        });

        return count($rows);
    }
}
