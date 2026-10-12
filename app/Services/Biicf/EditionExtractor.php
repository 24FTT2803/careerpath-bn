<?php

namespace App\Services\Biicf;

/**
 * Reads an uploaded edition into the shape the comparison works
 * against.
 *
 * An interface because how the book is read is a decision of its
 * own, and a changeable one: the framework's pages are laid out
 * by whoever designs that edition, and a reader tuned to one
 * printing is not guaranteed the next. What must not change is
 * what comes out, since the comparison, the review queue and
 * everything downstream are built on it.
 *
 * The shape:
 *
 *   [
 *     'job_roles' => [
 *       [
 *         'title' => string,
 *         'sub_sector' => string,
 *         'alternate_titles' => string[],
 *         'description' => string[],
 *         'trainings' => string[],
 *         'tiers' => string[],
 *         'entry_requirements' => [tier => [['raw_text' => string, ...]]],
 *         'competencies' => [['name' => string, 'type' => string,
 *                             'levels' => [tier => int]]],
 *       ],
 *     ],
 *     'competencies' => [
 *       ['name' => string, 'type' => string, 'definition' => string,
 *        'descriptors' => [...]],
 *     ],
 *   ]
 */
interface EditionExtractor
{
    /**
     * @param  string  $path  an absolute path to the uploaded file
     * @return array<string, mixed>
     *
     * @throws \RuntimeException when the file cannot be read as an edition
     */
    public function extract(string $path): array;

    /**
     * What this reader accepts, for the upload form to state and
     * the request to validate against.
     *
     * @return array<int, string>
     */
    public function extensions(): array;

    /**
     * How to describe it to whoever is uploading.
     */
    public function describe(): string;
}
