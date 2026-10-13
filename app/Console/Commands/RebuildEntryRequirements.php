<?php

namespace App\Console\Commands;

use App\Models\BiicfEdition;
use App\Models\BiicfJobRole;
use App\Services\Biicf\EditionExtractor;
use App\Services\Biicf\EntryRequirementWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Read the ways in to every role again, from the book itself.
 *
 * The book prints some roles' entry requirements once and others
 * under a heading for each level, and the reader used to give
 * every level all of them. A framework loaded before that was
 * fixed tells a Specialist they need the years asked of
 * Expert/Management, and gives IT Technician's three levels the
 * same requirement where the book gives each its own.
 *
 * Rebuilt from the book rather than the data file, because the
 * data file never held these in a usable shape — it kept them
 * as one run of prose per role. The book is already on record:
 * whichever edition is in use was uploaded with it.
 *
 * Writes nothing else. Everything a reading of the book would
 * otherwise overwrite — descriptions, courses, proficiencies,
 * and every decision made while reviewing an edition — is left
 * exactly as it is.
 */
class RebuildEntryRequirements extends Command
{
    protected $signature = 'biicf:rebuild-entry-requirements
                            {--book= : a PDF to read instead of the edition in use}
                            {--pretend : say what would change and write nothing}
                            {--memory=1G : how much memory to allow the parse}';

    protected $description = 'Read every role\'s entry requirements again from the reference book';

    public function handle(EditionExtractor $extractor, EntryRequirementWriter $writer): int
    {
        if (ini_get('memory_limit') !== '-1') {
            ini_set('memory_limit', (string) $this->option('memory'));
        }

        $book = $this->book();

        if ($book === null) {
            return self::FAILURE;
        }

        $this->info('Reading '.basename($book).'. This takes a while.');

        try {
            $edition = $extractor->extract($book);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $rows = [];
        $missing = [];

        foreach ($edition['job_roles'] as $entry) {
            $role = BiicfJobRole::where('title', $entry['title'])->first();

            if ($role === null) {
                $missing[] = $entry['title'];

                continue;
            }

            $byTier = $entry['entry_requirements'] ?? [];

            $rows[] = [
                $entry['title'],
                implode(', ', array_keys($byTier)),
                array_sum(array_map('count', $byTier)),
            ];

            if ($this->option('pretend')) {
                continue;
            }

            try {
                $writer->write($role, $byTier);
            } catch (RuntimeException $e) {
                $this->warn($e->getMessage());
            }
        }

        $this->newLine();
        $this->table(['Role', 'Levels', 'Ways in'], $rows);

        if ($missing !== []) {
            $this->warn(count($missing).' role(s) in the book are not in this framework, and were skipped.');
        }

        $this->info($this->option('pretend')
            ? 'Nothing was written.'
            : 'Entry requirements rebuilt.');

        return self::SUCCESS;
    }

    /**
     * The book to read: the one given, or the one the edition in
     * use was uploaded with.
     */
    private function book(): ?string
    {
        if ($this->option('book')) {
            if (! is_file((string) $this->option('book'))) {
                $this->error('There is no file at '.$this->option('book').'.');

                return null;
            }

            return (string) $this->option('book');
        }

        $edition = BiicfEdition::where('is_current', true)->first()
            ?? BiicfEdition::whereNotNull('applied_at')->orderByDesc('applied_at')->first();

        if ($edition?->source_file_path === null) {
            $this->error(
                'No edition in use has a book on record. Upload one on the editions '
                .'screen, or name a file with --book.'
            );

            return null;
        }

        if (! Storage::disk('local')->exists($edition->source_file_path)) {
            $this->error("The book recorded against {$edition->name} is no longer on disk.");

            return null;
        }

        $this->line("Reading the book recorded against {$edition->name}.");

        return Storage::disk('local')->path($edition->source_file_path);
    }
}
