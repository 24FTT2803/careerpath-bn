<?php

namespace App\Services\Biicf;

use RuntimeException;

/**
 * Picks the reader that suits the file.
 *
 * The book is what an administrator has, so the PDF is the way
 * in. JSON stays alongside it because a reader tuned to one
 * printing is not guaranteed the next: an edition laid out in a
 * way this one cannot follow can still be loaded by extracting
 * it elsewhere, rather than leaving the framework stuck on an
 * old book with no way forward but retyping it.
 */
class EditionExtractors implements EditionExtractor
{
    /** @var array<int, EditionExtractor> */
    private array $readers;

    public function __construct(EditionExtractor ...$readers)
    {
        $this->readers = $readers;
    }

    public function extract(string $path): array
    {
        return $this->for(pathinfo($path, PATHINFO_EXTENSION))->extract($path);
    }

    public function extensions(): array
    {
        return array_values(array_unique(array_merge(
            ...array_map(fn (EditionExtractor $reader) => $reader->extensions(), $this->readers)
        )));
    }

    public function describe(): string
    {
        return 'The framework as AITI publishes it, as a PDF — or an edition '
            .'already extracted to JSON, for a printing this cannot follow.';
    }

    private function for(string $extension): EditionExtractor
    {
        foreach ($this->readers as $reader) {
            if (in_array(strtolower($extension), $reader->extensions(), true)) {
                return $reader;
            }
        }

        throw new RuntimeException(
            'An edition is uploaded as '.implode(' or ', $this->extensions()).'.'
        );
    }
}
