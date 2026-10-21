<?php

namespace App\Console\Commands;

use App\Models\BiicfJobRole;
use App\Services\Biicf\WorkFunctionWriter;
use Illuminate\Console\Command;

/**
 * Write every role's duties again from the framework's data
 * file, and nothing else.
 *
 * One database ended up holding two and a half thousand duty
 * rows where the data describes eleven hundred: the same duty
 * groups two and three times over, each copy a reading from a
 * different day. Writing without clearing first did it, which
 * the seeder no longer does.
 *
 * Re-running the whole seeder would fix them and undo a great
 * deal else — an edition applied since, every decision made
 * while reviewing it — because the data file is older than
 * those decisions. Duties are the one thing an edition never
 * touches, so they can be rebuilt on their own without
 * disturbing any of it.
 */
class RebuildWorkFunctions extends Command
{
    protected $signature = 'biicf:rebuild-work-functions {--pretend : say what would change and write nothing}';

    protected $description = "Write every role's duties again from the framework's data file";

    public function handle(WorkFunctionWriter $writer): int
    {
        $path = database_path('data/biicf-job-roles.json');

        if (! is_file($path)) {
            $this->error("The framework's data file is not at {$path}.");

            return self::FAILURE;
        }

        /** @var array<int, array<string, mixed>> $entries */
        $entries = json_decode((string) file_get_contents($path), true);

        $before = (int) BiicfJobRole::query()->withCount('criticalWorkFunctions')->get()->sum('critical_work_functions_count');

        $expected = 0;
        $missing = [];

        foreach ($entries as $entry) {
            $expected += $this->countOf($entry['critical_work_functions'] ?? []);
        }

        $this->line("The framework holds {$before} duty rows; the data file describes {$expected}.");

        if ($this->option('pretend')) {
            $this->info('Nothing was written.');

            return self::SUCCESS;
        }

        $written = 0;

        foreach ($entries as $entry) {
            $role = BiicfJobRole::where('title', $entry['title'])->first();

            if ($role === null) {
                $missing[] = $entry['title'];

                continue;
            }

            $written += $writer->write($role, $entry['critical_work_functions'] ?? []);
        }

        $this->info("{$written} duty rows written.");

        if ($missing !== []) {
            $this->newLine();
            $this->warn(count($missing).' role(s) in the data file are not in this framework, and were skipped:');

            foreach ($missing as $title) {
                $this->line('  - '.$title);
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $functions
     */
    private function countOf(array $functions): int
    {
        $count = 0;

        foreach ($functions as $function) {
            $count += 1 + $this->countOf($function['children'] ?? []);
        }

        return $count;
    }
}
