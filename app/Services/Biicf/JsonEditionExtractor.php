<?php

namespace App\Services\Biicf;

use RuntimeException;

/**
 * Reads an edition that has already been extracted into JSON.
 *
 * The least clever reader there is, and the one the rest of the
 * pipeline was proved against: it checks the shape and hands it
 * on. Whatever reads the book itself — a parser, a model, a
 * person with a spreadsheet — produces this, so the review and
 * apply steps never have to care which.
 *
 * It is also the honest fallback. An edition laid out in a way
 * no reader here can follow can still be loaded, by extracting
 * it elsewhere, rather than leaving the framework stuck on an
 * old book.
 */
class JsonEditionExtractor implements EditionExtractor
{
    public function extract(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("There is no file at {$path}.");
        }

        try {
            $decoded = json_decode(
                (string) file_get_contents($path),
                true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new RuntimeException(
                'That file is not readable as JSON: '.$e->getMessage()
            );
        }

        return $this->check($decoded);
    }

    public function extensions(): array
    {
        return ['json'];
    }

    public function describe(): string
    {
        return 'An edition already extracted to JSON.';
    }

    /**
     * Refuse anything that is not an edition.
     *
     * A file that parses but holds the wrong thing would stage a
     * comparison proposing to empty the framework, which an
     * administrator could approve without ever seeing what it
     * really was.
     *
     * @return array<string, mixed>
     */
    private function check(mixed $decoded): array
    {
        if (! is_array($decoded)) {
            throw new RuntimeException('That file does not hold an edition.');
        }

        $roles = $decoded['job_roles'] ?? null;

        if (! is_array($roles) || $roles === []) {
            throw new RuntimeException(
                'That file names no job roles, so there is no edition in it.'
            );
        }

        foreach ($roles as $index => $role) {
            foreach (['title', 'sub_sector', 'tiers'] as $required) {
                if (! isset($role[$required])) {
                    throw new RuntimeException(
                        "The role at position {$index} has no {$required}."
                    );
                }
            }

            if (! is_array($role['tiers']) || $role['tiers'] === []) {
                throw new RuntimeException(
                    "{$role['title']} lists no tiers, and a role is entered at one."
                );
            }
        }

        return [
            'job_roles' => $roles,
            'competencies' => $decoded['competencies'] ?? [],
        ];
    }
}
