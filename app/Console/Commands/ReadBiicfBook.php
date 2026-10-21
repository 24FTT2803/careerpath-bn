<?php

namespace App\Console\Commands;

use App\Services\Biicf\PdfEditionExtractor;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Read the reference book from the command line.
 *
 * The book is three hundred pages and ten megabytes, and reading
 * it in PHP takes long enough and holds enough in memory that a
 * web request is the wrong place to do it. Here there is no
 * request to time out and no one waiting on a page to finish
 * loading.
 *
 * It writes what it read as JSON, which the edition upload also
 * accepts. So the long job happens once, here, and the upload
 * screen gets handed the result — the same review and the same
 * decisions, without a browser holding its breath through the
 * parse.
 */
class ReadBiicfBook extends Command
{
    protected $signature = 'biicf:read-book
                            {path : the reference book, as a PDF}
                            {--json= : write what was read to this file}
                            {--memory=1G : how much memory to allow the parse}';

    protected $description = 'Read a BIICF reference book and report what is in it';

    public function handle(PdfEditionExtractor $extractor): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("There is no file at {$path}.");

            return self::FAILURE;
        }

        /*
         * Raised deliberately, and only here. Reading three
         * hundred pages of PDF in PHP needs more room than the
         * default allows, and this is a command run once when a
         * new edition is published — not something serving a
         * page. Everything else keeps the limit it was given.
         *
         * An unlimited limit stays unlimited: setting a figure
         * over it would be lowering it, which is the opposite of
         * what this is for.
         */
        if (ini_get('memory_limit') !== '-1') {
            ini_set('memory_limit', (string) $this->option('memory'));
        }

        $this->info('Reading '.basename((string) $path).'. This takes a while.');

        $started = microtime(true);

        try {
            $edition = $extractor->extract((string) $path);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $roles = $edition['job_roles'];

        $this->newLine();
        $this->line(sprintf(
            '%d job roles, in %ds, using %dMB.',
            count($roles),
            (int) (microtime(true) - $started),
            (int) (memory_get_peak_usage(true) / 1024 / 1024)
        ));

        $this->table(
            ['Role', 'Sub-sector', 'Levels', 'Duties', 'Courses', 'Competencies'],
            array_map(fn (array $role) => [
                $role['title'],
                $role['sub_sector'],
                implode(', ', $role['tiers']),
                count($role['description'] ?? []),
                count($role['trainings'] ?? []),
                count($role['competencies'] ?? []),
            ], array_slice($roles, 0, 50))
        );

        /*
         * What it could not make sense of. These raise no
         * difference when the edition is compared, so the
         * framework keeps what it has — which is the right
         * outcome, but only if somebody knows it happened.
         */
        if ($edition['problems'] !== []) {
            $this->newLine();
            $this->warn(count($edition['problems']).' thing(s) could not be read:');

            foreach ($edition['problems'] as $problem) {
                $this->line('  - '.$problem);
            }
        }

        if ($this->option('json')) {
            $written = file_put_contents(
                $this->option('json'),
                json_encode($edition, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            if ($written === false) {
                $this->error('That could not be written to '.$this->option('json').'.');

                return self::FAILURE;
            }

            $this->newLine();
            $this->info('Written to '.$this->option('json').'. Upload that on the editions screen.');
        }

        return self::SUCCESS;
    }
}
